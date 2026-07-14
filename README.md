# parousiologio

## dependencies

- [phpmailer/phpmailer: ^7.1.1](https://packagist.org/packages/phpmailer/phpmailer) to send emails through smtp

- [google/apiclient: ^2.19.4](https://packagist.org/packages/google/apiclient) to login with google account

  - [guzzlehttp/guzzle: ^7.4.5](https://packagist.org/packages/guzzlehttp/guzzle) required but ^7.12.1 is safe
  - [guzzlehttp/psr7: ^2.6](https://packagist.org/packages/guzzlehttp/psr7) required but ^2.12.1 is safe

- [phpoffice/phpspreadsheet: ^1.30.6](https://packagist.org/packages/phpoffice/phpspreadsheet) to export data as a .xlsx file

- [whichbrowser/parser: ^2.1.8](https://packagist.org/packages/whichbrowser/parser) to parse the [User-Agent](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/User-Agent) header
