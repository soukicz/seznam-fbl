<?php
namespace Soukicz\SeznamFbl;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Api {
    protected $email;
    protected $password;
    protected $client;

    function __construct($email, $password) {
        $this->email = $email;
        $this->password = $password;
    }

    protected function getClient() {
        if(!$this->client) {
            $this->client = new Client([
                'cookies' => true,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36'
                ]
            ]);
            $this->client->get('https://fbl.seznam.cz/', ['allow_redirects' => false]);
            $this->client->get('https://fbl.seznam.cz/login/?url=/', ['allow_redirects' => false]);
            $this->client->get('https://login.szn.cz/', ['allow_redirects' => false]);

            $response = $this->client->post('https://login.szn.cz/api/v1/login', [
                'headers' => [
                    'Accept' => 'application/json;q=0.9,*/*;q=0.8',
                ],
                'json' => [
                    'password' => $this->password,
                    'remember' => false,
                    'return_url' => 'http://fbl.seznam.cz/',
                    'service' => 'napoveda',
                    'username' => $this->email
                ],
                'exceptions' => false
            ]);
            $responseData = json_decode($response->getBody(), true);
            if($response->getStatusCode() !== 200) {
                if(isset($responseData['message'])) {
                    throw new IOException($responseData['message'], $responseData['status']);
                }
            }

            $this->client->get($responseData['location'], ['allow_redirects' => false]);
            $this->client->get('http://fbl.seznam.cz/', ['allow_redirects' => false]);
            $this->client->get('https://fbl.seznam.cz/', ['allow_redirects' => false]);


        }
        return $this->client;
    }

    public function getDomains() {
        $crawler = $this->getFirstCrawler();

        /**
         * @var Domain[] $domains
         */
        $domains = [];
        $lastLink = null;
        do {
            $crawler->filter('table#domain-table tr')->each(function (Crawler $node, $index) use (&$domains) {
                if($index === 0) {
                    return null;
                }
                $td = $node->children();
                $domain = new Domain();
                $status = $td->eq(5)->text();
                if($status === 'active') {
                    $domain->setActive();
                } elseif($status === 'pending') {
                    $domain->setPending();
                }
                $domain
                    ->setHostname($td->eq(0)->text())
                    ->setSelector($td->eq(1)->text())
                    ->setHeader($td->eq(2)->text())
                    ->setRegex($td->eq(3)->text())
                    ->setConsumer($td->eq(4)->text());
                $domains[$domain->getHostname()] = $domain;
            });
            $link = $crawler->filter('img[src="/images/next.png"]')->parents()->eq(0)->link();

            $crawler = new Crawler((string)$this->getClient()->get($link->getUri())->getBody(), $link->getUri());
            if($link->getUri() == $lastLink) {
                break;
            }
            $lastLink = $link->getUri();
        } while (true);

        return $domains;
    }

    private function getFirstCrawler() {
        return new Crawler((string)$this->getClient()->get('https://fbl.seznam.cz/feedbackloop')->getBody(), 'https://fbl.seznam.cz/feedbackloop');
    }

    public function addDomain(Domain $domain, $confirmationType) {
        $crawler = $this->getFirstCrawler();

        $token = $crawler->filter('input[name=csrf]')->eq(0)->attr('value');
        $action = 'https://fbl.seznam.cz/addDomain';

        $response = $this->getClient()->post($action, [
            'form_params' => [
                'domain' => $domain->getHostname(),
                'selector' => $domain->getSelector(),
                'header' => $domain->getHeader(),
                'regex' => $domain->getRegex(),
                'consumer' => $domain->getConsumer(),
                'response' => $confirmationType,
                'csrf' => $token,
            ]
        ]);

        $crawler = new Crawler((string)$response->getBody());

        $error = $crawler->filter('div.notify.error');
        if($error->count()) {
            throw new IOException($error->eq(0)->text());
        }
    }
}
