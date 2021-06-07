# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.5](https://github.com/ecphp/cas-lib/compare/1.1.4...1.1.5)

### Merged

- Bump actions/cache from 2.1.5 to 2.1.6 [`#24`](https://github.com/ecphp/cas-lib/pull/24)
- ECPHP-152: Do not update `service` parameter [`#26`](https://github.com/ecphp/cas-lib/pull/26)
- Update vimeo/psalm requirement from ^3.12 to ^3.12 || ^4.0 [`#17`](https://github.com/ecphp/cas-lib/pull/17)
- Bump actions/cache from 2.1.4 to 2.1.5 [`#22`](https://github.com/ecphp/cas-lib/pull/22)
- Bump actions/cache from v2 to v2.1.4 [`#20`](https://github.com/ecphp/cas-lib/pull/20)

### Commits

- CI: Enable automatic changelog parsing on release. [`11c8098`](https://github.com/ecphp/cas-lib/commit/11c809881875c875e61c4e7173d5f1727578b11a)
- Add Docker stack for changelog generation. [`5bc2c26`](https://github.com/ecphp/cas-lib/commit/5bc2c26b71b1d621d712b6732a3d9de7d50493f5)
- Do not alter the service parameter with renew and gateway parameters. [`a8c0398`](https://github.com/ecphp/cas-lib/commit/a8c03986ebd2d40a309580500dc3eb2b4d988ee7)
- Update tests. [`1f2ca88`](https://github.com/ecphp/cas-lib/commit/1f2ca889d42653385e3570ada05c5a051bb63822)

## [1.1.4](https://github.com/ecphp/cas-lib/compare/1.1.3...1.1.4) - 2020-08-06

### Merged

- Remove obsolete libxml calls. [`#16`](https://github.com/ecphp/cas-lib/pull/16)

## [1.1.3](https://github.com/ecphp/cas-lib/compare/1.1.2...1.1.3) - 2020-08-04

### Commits

- Remove obsolete abstract class. [`6faa301`](https://github.com/ecphp/cas-lib/commit/6faa3012f64ac5d646fd08a2b775b84c2e1e97d8)

## [1.1.2](https://github.com/ecphp/cas-lib/compare/1.1.1...1.1.2) - 2020-08-03

### Merged

- Parse XML with a contrib library and get rid of custom class. [`#13`](https://github.com/ecphp/cas-lib/pull/13)

### Commits

- Update composer.json. [`cf130cc`](https://github.com/ecphp/cas-lib/commit/cf130ccfc9743d3c6d05846647ae2c8e06631927)

## [1.1.1](https://github.com/ecphp/cas-lib/compare/1.1.0...1.1.1) - 2020-07-28

### Merged

- Detect ProxyFailure service response. [`#14`](https://github.com/ecphp/cas-lib/pull/14)

### Commits

- Add Psalm, Infection and Insights reports. [`cd0b8d0`](https://github.com/ecphp/cas-lib/commit/cd0b8d0996420c76779652b6c0693d59c8512d2e)
- Prevent builds from failing on Windows. [`48d5832`](https://github.com/ecphp/cas-lib/commit/48d5832ad705c15ea46f3cbc2b76daabd3d061ce)

## [1.1.0](https://github.com/ecphp/cas-lib/compare/1.0.8...1.1.0) - 2020-07-23

### Merged

- Issue #11: Inject the Introspector object. [`#12`](https://github.com/ecphp/cas-lib/pull/12)

### Commits

- Issue #11: Add CasInterface::detect() method. [`0dc346f`](https://github.com/ecphp/cas-lib/commit/0dc346ff0401b0a412ab8386f4fc08ed35153f5f)
- Issue #11: Add new IntrospectionInterface::withParsedResponse() method. [`f43c641`](https://github.com/ecphp/cas-lib/commit/f43c64174e6af6622f90dc1d4d55f9328471057e)

## [1.0.8](https://github.com/ecphp/cas-lib/compare/1.0.7...1.0.8) - 2020-07-23

### Commits

- Update composer.json. [`c975348`](https://github.com/ecphp/cas-lib/commit/c9753483ed9728fee1d1e04015aa5b05f9140a87)

## [1.0.7](https://github.com/ecphp/cas-lib/compare/1.0.6...1.0.7) - 2020-07-23

### Commits

- Update Grumphp configuration. [`679a8f0`](https://github.com/ecphp/cas-lib/commit/679a8f018f73608c521a2ebe5e1a8b79b2a47ab1)

## [1.0.6](https://github.com/ecphp/cas-lib/compare/1.0.5...1.0.6) - 2020-07-23

### Commits

- Fix CS. [`ac9bfaf`](https://github.com/ecphp/cas-lib/commit/ac9bfaf74cdeb6ab03583b5ada3a8d56d0d3a8e6)
- Update Grumphp configuration. [`a761923`](https://github.com/ecphp/cas-lib/commit/a761923c94dddcc486b7a3dbe3955c7be6081d3e)
- Update composer.json. [`1ed3a9c`](https://github.com/ecphp/cas-lib/commit/1ed3a9c9c46520a4c9c9d9cf4542ce946ebd2077)

## [1.0.5](https://github.com/ecphp/cas-lib/compare/1.0.4...1.0.5) - 2020-06-19

### Commits

- Rely on league/uri-query-parser for parsing query parameters. [`3ad02b2`](https://github.com/ecphp/cas-lib/commit/3ad02b2b98553a62f54a7f80941d11e2b2fa34b1)
- Update composer.json. [`f8a357a`](https://github.com/ecphp/cas-lib/commit/f8a357a1722281998dff75aed834f742ec6eb0e8)

## [1.0.4](https://github.com/ecphp/cas-lib/compare/1.0.3...1.0.4) - 2020-06-11

### Merged

- Accept parameters in CasInterface::authenticate() and CasInterface::supportAuthentication() [`#10`](https://github.com/ecphp/cas-lib/pull/10)
- Update nyholm/psr7-server requirement from ^0.4.1 to ^0.4.1 || ^1.0.0 [`#8`](https://github.com/ecphp/cas-lib/pull/8)
- Bump actions/cache from v1 to v2 [`#7`](https://github.com/ecphp/cas-lib/pull/7)

### Commits

- Add Dependabot configuration. [`99b457e`](https://github.com/ecphp/cas-lib/commit/99b457ed40e675037a83cae6187f6146075d7ebb)
- Use ternary operator (code style). [`063adf2`](https://github.com/ecphp/cas-lib/commit/063adf2e2f44fd2a4fbd442c7c4240feab2fc264)

## [1.0.3](https://github.com/ecphp/cas-lib/compare/1.0.2...1.0.3) - 2020-06-09

### Merged

- Prevent query string parameters to be altered by providing a custom parse_str method. [`#6`](https://github.com/ecphp/cas-lib/pull/6)

### Commits

- Minor update on Uri::removeParams(). [`79a3217`](https://github.com/ecphp/cas-lib/commit/79a321739ddb628e20a6aa87922144a591676610)

## [1.0.2](https://github.com/ecphp/cas-lib/compare/1.0.1...1.0.2) - 2020-05-07

### Merged

- Bump drupol/php-conventions, with security checker included by default [`#4`](https://github.com/ecphp/cas-lib/pull/4)

## [1.0.1](https://github.com/ecphp/cas-lib/compare/1.0.0...1.0.1) - 2020-04-28

### Commits

- Issue #1: Error when using array query parameters [`cb46874`](https://github.com/ecphp/cas-lib/commit/cb468747201e5b22bcb09d74569feba9a0b2f423)

## 1.0.0 - 2020-01-30

### Commits

- Migrate to CAS Lib. [`ecfb197`](https://github.com/ecphp/cas-lib/commit/ecfb197bf695e2a40c6d76b0f1ce24c3c5e8ab0e)
- Update Github actions configuration. [`a0dc9b6`](https://github.com/ecphp/cas-lib/commit/a0dc9b632b9de10fc3d7a160e8b31c3995809a82)
- Update Github actions configuration. [`dc6b991`](https://github.com/ecphp/cas-lib/commit/dc6b99169e09e5d189d60c6dfdcd334d0a159daa)
- Update Github actions configuration. [`4cbb590`](https://github.com/ecphp/cas-lib/commit/4cbb5905e79dd20e801b357aca10b013298911ec)
- Add scrutinizer/ocular. [`84ec9bd`](https://github.com/ecphp/cas-lib/commit/84ec9bd6bf00cf2a8eb726133fe39f89cfe64815)
- Update README. [`bcde465`](https://github.com/ecphp/cas-lib/commit/bcde465e0c18aba85edc3a322bf3413b07bae725)
- Update Github actions configuration. [`fa238d5`](https://github.com/ecphp/cas-lib/commit/fa238d5da71cb6d08bbfd09d869e5fd2f97d6fd8)
- Update composer.json. [`afade37`](https://github.com/ecphp/cas-lib/commit/afade376412806b29173b6f0e0d29faeeb821800)
- Update composer.json. [`56cb536`](https://github.com/ecphp/cas-lib/commit/56cb5363c9ca8b60ca5cd48801c71732b86848e2)
- Fix builds with lowest deps. [`f5a46cb`](https://github.com/ecphp/cas-lib/commit/f5a46cb2f3643ad069ec68dec5ee83f8895665ca)
- Bump versions. [`18187da`](https://github.com/ecphp/cas-lib/commit/18187dada1b72d046241c5a43c171fe4005b65b3)
- Bump versions. [`da58a9c`](https://github.com/ecphp/cas-lib/commit/da58a9ced7d50b9da366058f92123904822cf733)
- Fix test. [`85b7e99`](https://github.com/ecphp/cas-lib/commit/85b7e9922066802c2f085038bf5bf6bbe77dd7c4)
- Update actions. [`cbf82d4`](https://github.com/ecphp/cas-lib/commit/cbf82d4ea8700f7380b57dce5c7d25f560d0f2ff)
- Update actions. [`120976f`](https://github.com/ecphp/cas-lib/commit/120976f03422b23068cc7a080dd2ab16a01e69bc)
- Update actions. [`75bdd9a`](https://github.com/ecphp/cas-lib/commit/75bdd9a922b8a782eaeeb1be7261c4cb78ad3201)
- Remove obsolete static files. [`435715c`](https://github.com/ecphp/cas-lib/commit/435715c1956b8fbc08d22093130c369fb8ba0356)
- Fix PHPStan warnings. [`dc425a7`](https://github.com/ecphp/cas-lib/commit/dc425a79f0e44733110b4f8989f0e8ca97644d36)
- Update Login redirect workflow. [`150f5ae`](https://github.com/ecphp/cas-lib/commit/150f5aed26f90fd16b6c16d051333aad34db4e83)
- Remove obsolete dev packages. [`c4eddd8`](https://github.com/ecphp/cas-lib/commit/c4eddd8fc8c7b3885f27f06b72303f2da99038b4)
- Update documentation. [`ec812aa`](https://github.com/ecphp/cas-lib/commit/ec812aab04a54d16d898f5f30369e848744eba35)
- Initial commit. [`b89f878`](https://github.com/ecphp/cas-lib/commit/b89f878baf12866e007ac4a13af19bbcabafda30)
