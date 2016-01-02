<?php
namespace Simplia\SeznamFbl;

use Goutte\Client;
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
            $this->client = new Client();
            $crawler = $this->client->request('GET', 'https://fbl.seznam.cz/');
            $crawler = $this->client->click($crawler->selectLink('Přihlásit se')->link());
            $form = $crawler->selectButton('Přihlásit se')->form();
            $crawler = $this->client->submit($form, [
                'username' => $this->email,
                'password' => $this->password,
            ]);
            $error = $crawler->filter('#error strong');
            if($error->count()) {
                throw new IOException($error->text());
            }
        }
        return $this->client;
    }

    public function getDomains() {
        $crawler = $this->getClient()->request('GET', 'https://fbl.seznam.cz/feedbackloop');
        if($crawler->selectButton('Přihlásit se')->count()) {
            $crawler = $crawler->selectButton('Přihlásit se')->link();
        }
        $domains = [];
        $lastLink = null;
        do {
            $list = $crawler->filter('table#domain-table tr')->each(function (Crawler $node, $index) {
                if($index === 0) {
                    return null;
                }
                $td = $node->children();
                return [
                    'domain' => $td->eq(0)->text(),
                    'selector' => $td->eq(1)->text(),
                    'header' => $td->eq(2)->text(),
                    'pattern' => $td->eq(3)->text(),
                ];
            });
            $domains = array_merge($domains, $list);
            $link = $crawler->filter('img[src="/images/next.png"]')->parents()->eq(0)->link();
            $crawler = $this->getClient()->click($link);
            if($link->getUri() == $lastLink) {
                break;
            }
            $lastLink = $link->getUri();
        } while (true);

        $output = [];
        foreach (array_filter($domains, 'is_array') as $domain) {
            $output[$domain['domain']] = [
                'selector' => $domain['selector'],
                'header' => $domain['header'],
                'pattern' => $domain['pattern'],
            ];
        }

        return $output;
    }

    public function addDomain($domain) {
        $crawler = $this->getClient()->request('GET', 'https://fbl.seznam.cz/feedbackloop');
        if($crawler->selectButton('Přihlásit se')->count()) {
            $crawler = $crawler->selectButton('Přihlásit se')->link();
        }
        $this->getClient()->submit($crawler->filter('#domain-save')->form(), [
            'domain' => $domain,
            'selector' => '*',
            'header' => 'X-CampaingId',
            'regex' => '([\w^\-]+)\-',
            'consumer' => 'bounce@simpliashop.cz',
            'response' => 'abuse',
        ]);
    }
}
