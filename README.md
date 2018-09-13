# Coinpaprika Wordpress Plugin

## Features

### Ticker widget

Use this widget to display most important metrics for one selected cryptocurrency. Available in widgets section in Wordpress settings.

![Ticker widget](coinpaprika/assets/screenshot-1.png)

### Shortcodes

Use this shortcode
`[coinpaprika coin=eth-ethereum in=usd]` in post/page text to get the fresh price of Ethereum in US Dollars.

All options: `[coinpaprika coin="COIN-ID" quote="usd|btc|eth" icon="true|false" metric="price|volume24h|marketcap|ath" change="true|false"]`

What could be used as `COIN-ID`?
* `btc-bitcoin` for Bitcoin
* `eth-ethereum` for Ethereum
* `bch-bitcoin-cash` for Bitcoin Cash
* `eos-eos` for EOS
* `xlm-stellar` for Stellar

etc... for other coins listed on Coinpaprika.

![Shortcodes in action](coinpaprika/assets/screenshot-3.png)

## How to install this plugin on your Wordpress site?

Go to `Plugins` > `Add new` section in Admin Panel and search for `Coinpaprika` or download the installation package from [Plugins Directory](https://wordpress.org/plugins/coinpaprika/).

## Coding standards
[Wordpress PHP Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php)

## Local testing

You can use Docker containers to perform one-click setup of Wordpress installation with our plugin.

To start wordpress run `docker-compose up` in plugin folder and open [localhost:8080](http://localhost:8080/) website.

After the 1st run, you need to activate our plugin on Plugins list.
