# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0](https://code.europa.eu/ecphp/cas-lib/compare/1.1.9...1.2.0)

### Merged

- fix: make sure handlers `parameters` are correctly handled [`#41`](https://code.europa.eu/ecphp/cas-lib/pull/41)
- ci: Update dev workflow. [`#34`](https://code.europa.eu/ecphp/cas-lib/pull/34)

### Commits

- chore: fix `README` badge [`29c0425`](https://code.europa.eu/ecphp/cas-lib/commit/29c042511670a14d927e4182c88cbf8e67f0c035)
- chore: update license [`f958de1`](https://code.europa.eu/ecphp/cas-lib/commit/f958de1e6df8c3ab8d899742d06dcf06511cb4f5)
- Remove obsolete annotation. [`7e19167`](https://code.europa.eu/ecphp/cas-lib/commit/7e19167f73504b53a0b1e4b266e037e33d17e99d)
- ci: Update Scrutinizer configuration. [`5a51803`](https://code.europa.eu/ecphp/cas-lib/commit/5a5180357b6fa38c8856320f7a43b4010d5bc661)
- chore: Remove Docker stuff. [`6003b4b`](https://code.europa.eu/ecphp/cas-lib/commit/6003b4b8244c7df4cb4ee1c343198ef43455f030)

## [1.1.9](https://code.europa.eu/ecphp/cas-lib/compare/1.1.8...1.1.9) - 2022-05-02

### Merged

- Update phpstan/phpstan-strict-rules requirement from ^0.12 to ^0.12 || ^1.0 [`#29`](https://code.europa.eu/ecphp/cas-lib/pull/29)
- Bump actions/cache from 2.1.7 to 3 [`#31`](https://code.europa.eu/ecphp/cas-lib/pull/31)

### Commits

- docs: Update changelog. [`643b216`](https://code.europa.eu/ecphp/cas-lib/commit/643b216c7461a62aebb1389cbc4741b22089e589)
- chore: Update `composer.json`. [`e76d314`](https://code.europa.eu/ecphp/cas-lib/commit/e76d3149a9da442c96c088f7c95b30a17f293cdc)
- ci: Tests on PHP 7.4, 8.0 and 8.1. [`24e737e`](https://code.europa.eu/ecphp/cas-lib/commit/24e737e0efa63b107ff7a6d14162ce070354ffe8)
- refactor: Add types. [`eaff0e6`](https://code.europa.eu/ecphp/cas-lib/commit/eaff0e6957afab9bb7db009bbc83ec49532f4056)
- chore: Update Psalm configuration file. [`01f9f16`](https://code.europa.eu/ecphp/cas-lib/commit/01f9f1670ed1380e6c74d0a989405e974f5040d0)
- chore: Add `xdebug` extension in `require-dev`. [`2957660`](https://code.europa.eu/ecphp/cas-lib/commit/295766021cdedc09a0ea51ac423ceb79c2fa1ff9)
- Update phpstan/phpstan-strict-rules requirement || ^1.0 [`caa3cca`](https://code.europa.eu/ecphp/cas-lib/commit/caa3ccab45e9f163037d2d6ba97b9eedd882ee72)

## [1.1.8](https://code.europa.eu/ecphp/cas-lib/compare/1.1.7...1.1.8) - 2022-02-18

### Commits

- docs: Update changelog. [`2307029`](https://code.europa.eu/ecphp/cas-lib/commit/230702978257f77ec145150d35841169a19739ec)
- chore: Relax dependencies. [`8aba637`](https://code.europa.eu/ecphp/cas-lib/commit/8aba637c8be0a525b07d4b75bfdb2ecfcb35fee8)

## [1.1.7](https://code.europa.eu/ecphp/cas-lib/compare/1.1.6...1.1.7) - 2022-01-24

### Merged

- Bump actions/cache from 2.1.6 to 2.1.7 [`#30`](https://code.europa.eu/ecphp/cas-lib/pull/30)
- Update infection/infection requirement from ^0.23 to ^0.23 || ^0.24 [`#28`](https://code.europa.eu/ecphp/cas-lib/pull/28)

### Commits

- docs: Add/update CHANGELOG. [`a9ff174`](https://code.europa.eu/ecphp/cas-lib/commit/a9ff1741077b172522d340cf831a820563247c73)
- fix: Replace `league/uri-query-parser` with `league/uri-components`. [`167aade`](https://code.europa.eu/ecphp/cas-lib/commit/167aade628b24e04518489b0860e750a914187f8)
- fix: ECPHP-179: Autofix licence holder. [`2c0a71b`](https://code.europa.eu/ecphp/cas-lib/commit/2c0a71b05ba3798854d87e62e3583324ad0b4a94)
- Revert "ci: Disable builds on macOS until phpspec/phpspec#1380 is fixed." [`c15617a`](https://code.europa.eu/ecphp/cas-lib/commit/c15617a3bbc607b7a4540bb8ff53a301c2be3740)

## [1.1.6](https://code.europa.eu/ecphp/cas-lib/compare/1.1.5...1.1.6) - 2021-07-05

### Merged

- Use `ecphp/php-conventions`. [`#25`](https://code.europa.eu/ecphp/cas-lib/pull/25)

### Commits

- docs: Add/update CHANGELOG. [`798a5d4`](https://code.europa.eu/ecphp/cas-lib/commit/798a5d44a634756a62270bd6302db50ea15025ce)
- Update composer.json. [`2af8b60`](https://code.europa.eu/ecphp/cas-lib/commit/2af8b60b473fe0f76bc1ca2fe049b6d13beef260)
- ci: Disable builds on macOS until phpspec/phpspec#1380 is fixed. [`29b7f42`](https://code.europa.eu/ecphp/cas-lib/commit/29b7f4244fae42208d6df0c1036e4498218a61db)
- chore: Update .gitattributes. [`e414bbb`](https://code.europa.eu/ecphp/cas-lib/commit/e414bbb465270a8913c3962a3c3898c36acfddb0)
- chore: Drop support for PHP &lt; 7.4. [`b1e4a30`](https://code.europa.eu/ecphp/cas-lib/commit/b1e4a30ae40ea951c39c89d3e3594cb8248c0c25)
- refactor: Autofix code style. [`b97217e`](https://code.europa.eu/ecphp/cas-lib/commit/b97217eeac4a637dc8822717d4f4ff3fe1cc2c2b)
- chore: Update Grumphp configuration. [`6207426`](https://code.europa.eu/ecphp/cas-lib/commit/62074263371df3184f505789b31eecced41da423)
- chore: Update .gitignore and .gitattributes. [`f4a4e48`](https://code.europa.eu/ecphp/cas-lib/commit/f4a4e4881b7fdd538d59aef988f892bd051abec3)
- chore: Upgrade dev-dependencies. [`51dea37`](https://code.europa.eu/ecphp/cas-lib/commit/51dea37d03ca7f351e2ce481200b2d43032efe34)
- chore: Add missing packages. [`75918f3`](https://code.europa.eu/ecphp/cas-lib/commit/75918f3b16f22de8756a430ae55e779321843b03)
- chore: Use ecphp/php-conventions. [`fae6040`](https://code.europa.eu/ecphp/cas-lib/commit/fae60406d98f2ee0dc15464a70c23008af8e3af1)
- chore: Proper indentation. [`23abcbc`](https://code.europa.eu/ecphp/cas-lib/commit/23abcbc127765c5974e23f0f12ef9a5f3b7f0d73)

## [1.1.5](https://code.europa.eu/ecphp/cas-lib/compare/1.1.4...1.1.5) - 2021-06-07

### Merged

- Bump actions/cache from 2.1.5 to 2.1.6 [`#24`](https://code.europa.eu/ecphp/cas-lib/pull/24)
- ECPHP-152: Do not update `service` parameter [`#26`](https://code.europa.eu/ecphp/cas-lib/pull/26)
- Update vimeo/psalm requirement from ^3.12 to ^3.12 || ^4.0 [`#17`](https://code.europa.eu/ecphp/cas-lib/pull/17)
- Bump actions/cache from 2.1.4 to 2.1.5 [`#22`](https://code.europa.eu/ecphp/cas-lib/pull/22)
- Bump actions/cache from v2 to v2.1.4 [`#20`](https://code.europa.eu/ecphp/cas-lib/pull/20)

### Commits

- docs: Add Changelog.md. [`3b6e53f`](https://code.europa.eu/ecphp/cas-lib/commit/3b6e53f82b43650a592311b1f02cc47a7a4cc77d)
- CI: Enable automatic changelog parsing on release. [`11c8098`](https://code.europa.eu/ecphp/cas-lib/commit/11c809881875c875e61c4e7173d5f1727578b11a)
- Add Docker stack for changelog generation. [`5bc2c26`](https://code.europa.eu/ecphp/cas-lib/commit/5bc2c26b71b1d621d712b6732a3d9de7d50493f5)
- Do not alter the service parameter with renew and gateway parameters. [`a8c0398`](https://code.europa.eu/ecphp/cas-lib/commit/a8c03986ebd2d40a309580500dc3eb2b4d988ee7)
- Update tests. [`1f2ca88`](https://code.europa.eu/ecphp/cas-lib/commit/1f2ca889d42653385e3570ada05c5a051bb63822)

## [1.1.4](https://code.europa.eu/ecphp/cas-lib/compare/1.1.3...1.1.4) - 2020-08-06

### Merged

- Remove obsolete libxml calls. [`#16`](https://code.europa.eu/ecphp/cas-lib/pull/16)

## [1.1.3](https://code.europa.eu/ecphp/cas-lib/compare/1.1.2...1.1.3) - 2020-08-04

### Commits

- Remove obsolete abstract class. [`6faa301`](https://code.europa.eu/ecphp/cas-lib/commit/6faa3012f64ac5d646fd08a2b775b84c2e1e97d8)

## [1.1.2](https://code.europa.eu/ecphp/cas-lib/compare/1.1.1...1.1.2) - 2020-08-03

### Merged

- Parse XML with a contrib library and get rid of custom class. [`#13`](https://code.europa.eu/ecphp/cas-lib/pull/13)

### Commits

- Update composer.json. [`cf130cc`](https://code.europa.eu/ecphp/cas-lib/commit/cf130ccfc9743d3c6d05846647ae2c8e06631927)

## [1.1.1](https://code.europa.eu/ecphp/cas-lib/compare/1.1.0...1.1.1) - 2020-07-28

### Merged

- Detect ProxyFailure service response. [`#14`](https://code.europa.eu/ecphp/cas-lib/pull/14)

### Commits

- Add Psalm, Infection and Insights reports. [`cd0b8d0`](https://code.europa.eu/ecphp/cas-lib/commit/cd0b8d0996420c76779652b6c0693d59c8512d2e)
- Prevent builds from failing on Windows. [`48d5832`](https://code.europa.eu/ecphp/cas-lib/commit/48d5832ad705c15ea46f3cbc2b76daabd3d061ce)

## [1.1.0](https://code.europa.eu/ecphp/cas-lib/compare/1.0.8...1.1.0) - 2020-07-23

### Merged

- Issue #11: Inject the Introspector object. [`#12`](https://code.europa.eu/ecphp/cas-lib/pull/12)

### Commits

- Issue #11: Add CasInterface::detect() method. [`0dc346f`](https://code.europa.eu/ecphp/cas-lib/commit/0dc346ff0401b0a412ab8386f4fc08ed35153f5f)
- Issue #11: Add new IntrospectionInterface::withParsedResponse() method. [`f43c641`](https://code.europa.eu/ecphp/cas-lib/commit/f43c64174e6af6622f90dc1d4d55f9328471057e)

## [1.0.8](https://code.europa.eu/ecphp/cas-lib/compare/1.0.7...1.0.8) - 2020-07-23

### Commits

- Update composer.json. [`c975348`](https://code.europa.eu/ecphp/cas-lib/commit/c9753483ed9728fee1d1e04015aa5b05f9140a87)

## [1.0.7](https://code.europa.eu/ecphp/cas-lib/compare/1.0.6...1.0.7) - 2020-07-23

### Commits

- Update Grumphp configuration. [`679a8f0`](https://code.europa.eu/ecphp/cas-lib/commit/679a8f018f73608c521a2ebe5e1a8b79b2a47ab1)

## [1.0.6](https://code.europa.eu/ecphp/cas-lib/compare/1.0.5...1.0.6) - 2020-07-23

### Commits

- Fix CS. [`ac9bfaf`](https://code.europa.eu/ecphp/cas-lib/commit/ac9bfaf74cdeb6ab03583b5ada3a8d56d0d3a8e6)
- Update Grumphp configuration. [`a761923`](https://code.europa.eu/ecphp/cas-lib/commit/a761923c94dddcc486b7a3dbe3955c7be6081d3e)
- Update composer.json. [`1ed3a9c`](https://code.europa.eu/ecphp/cas-lib/commit/1ed3a9c9c46520a4c9c9d9cf4542ce946ebd2077)

## [1.0.5](https://code.europa.eu/ecphp/cas-lib/compare/1.0.4...1.0.5) - 2020-06-19

### Commits

- Rely on league/uri-query-parser for parsing query parameters. [`3ad02b2`](https://code.europa.eu/ecphp/cas-lib/commit/3ad02b2b98553a62f54a7f80941d11e2b2fa34b1)
- Update composer.json. [`f8a357a`](https://code.europa.eu/ecphp/cas-lib/commit/f8a357a1722281998dff75aed834f742ec6eb0e8)

## [1.0.4](https://code.europa.eu/ecphp/cas-lib/compare/1.0.3...1.0.4) - 2020-06-11

### Merged

- Accept parameters in CasInterface::authenticate() and CasInterface::supportAuthentication() [`#10`](https://code.europa.eu/ecphp/cas-lib/pull/10)
- Update nyholm/psr7-server requirement from ^0.4.1 to ^0.4.1 || ^1.0.0 [`#8`](https://code.europa.eu/ecphp/cas-lib/pull/8)
- Bump actions/cache from v1 to v2 [`#7`](https://code.europa.eu/ecphp/cas-lib/pull/7)

### Commits

- Add Dependabot configuration. [`99b457e`](https://code.europa.eu/ecphp/cas-lib/commit/99b457ed40e675037a83cae6187f6146075d7ebb)
- Use ternary operator (code style). [`063adf2`](https://code.europa.eu/ecphp/cas-lib/commit/063adf2e2f44fd2a4fbd442c7c4240feab2fc264)

## [1.0.3](https://code.europa.eu/ecphp/cas-lib/compare/1.0.2...1.0.3) - 2020-06-09

### Merged

- Prevent query string parameters to be altered by providing a custom parse_str method. [`#6`](https://code.europa.eu/ecphp/cas-lib/pull/6)

### Commits

- Minor update on Uri::removeParams(). [`79a3217`](https://code.europa.eu/ecphp/cas-lib/commit/79a321739ddb628e20a6aa87922144a591676610)

## [1.0.2](https://code.europa.eu/ecphp/cas-lib/compare/1.0.1...1.0.2) - 2020-05-07

### Merged

- Bump drupol/php-conventions, with security checker included by default [`#4`](https://code.europa.eu/ecphp/cas-lib/pull/4)

## [1.0.1](https://code.europa.eu/ecphp/cas-lib/compare/1.0.0...1.0.1) - 2020-04-28

### Commits

- Issue #1: Error when using array query parameters [`cb46874`](https://code.europa.eu/ecphp/cas-lib/commit/cb468747201e5b22bcb09d74569feba9a0b2f423)

## 1.0.0 - 2020-01-30

### Commits

- Migrate to CAS Lib. [`ecfb197`](https://code.europa.eu/ecphp/cas-lib/commit/ecfb197bf695e2a40c6d76b0f1ce24c3c5e8ab0e)
- Update Github actions configuration. [`a0dc9b6`](https://code.europa.eu/ecphp/cas-lib/commit/a0dc9b632b9de10fc3d7a160e8b31c3995809a82)
- Update Github actions configuration. [`dc6b991`](https://code.europa.eu/ecphp/cas-lib/commit/dc6b99169e09e5d189d60c6dfdcd334d0a159daa)
- Update Github actions configuration. [`4cbb590`](https://code.europa.eu/ecphp/cas-lib/commit/4cbb5905e79dd20e801b357aca10b013298911ec)
- Add scrutinizer/ocular. [`84ec9bd`](https://code.europa.eu/ecphp/cas-lib/commit/84ec9bd6bf00cf2a8eb726133fe39f89cfe64815)
- Update README. [`bcde465`](https://code.europa.eu/ecphp/cas-lib/commit/bcde465e0c18aba85edc3a322bf3413b07bae725)
- Update Github actions configuration. [`fa238d5`](https://code.europa.eu/ecphp/cas-lib/commit/fa238d5da71cb6d08bbfd09d869e5fd2f97d6fd8)
- Update composer.json. [`afade37`](https://code.europa.eu/ecphp/cas-lib/commit/afade376412806b29173b6f0e0d29faeeb821800)
- Update composer.json. [`56cb536`](https://code.europa.eu/ecphp/cas-lib/commit/56cb5363c9ca8b60ca5cd48801c71732b86848e2)
- Fix builds with lowest deps. [`f5a46cb`](https://code.europa.eu/ecphp/cas-lib/commit/f5a46cb2f3643ad069ec68dec5ee83f8895665ca)
- Bump versions. [`18187da`](https://code.europa.eu/ecphp/cas-lib/commit/18187dada1b72d046241c5a43c171fe4005b65b3)
- Bump versions. [`da58a9c`](https://code.europa.eu/ecphp/cas-lib/commit/da58a9ced7d50b9da366058f92123904822cf733)
- Fix test. [`85b7e99`](https://code.europa.eu/ecphp/cas-lib/commit/85b7e9922066802c2f085038bf5bf6bbe77dd7c4)
- Update actions. [`cbf82d4`](https://code.europa.eu/ecphp/cas-lib/commit/cbf82d4ea8700f7380b57dce5c7d25f560d0f2ff)
- Update actions. [`120976f`](https://code.europa.eu/ecphp/cas-lib/commit/120976f03422b23068cc7a080dd2ab16a01e69bc)
- Update actions. [`75bdd9a`](https://code.europa.eu/ecphp/cas-lib/commit/75bdd9a922b8a782eaeeb1be7261c4cb78ad3201)
- Remove obsolete static files. [`435715c`](https://code.europa.eu/ecphp/cas-lib/commit/435715c1956b8fbc08d22093130c369fb8ba0356)
- Fix PHPStan warnings. [`dc425a7`](https://code.europa.eu/ecphp/cas-lib/commit/dc425a79f0e44733110b4f8989f0e8ca97644d36)
- Update Login redirect workflow. [`150f5ae`](https://code.europa.eu/ecphp/cas-lib/commit/150f5aed26f90fd16b6c16d051333aad34db4e83)
- Remove obsolete dev packages. [`c4eddd8`](https://code.europa.eu/ecphp/cas-lib/commit/c4eddd8fc8c7b3885f27f06b72303f2da99038b4)
- Update documentation. [`ec812aa`](https://code.europa.eu/ecphp/cas-lib/commit/ec812aab04a54d16d898f5f30369e848744eba35)
- Initial commit. [`b89f878`](https://code.europa.eu/ecphp/cas-lib/commit/b89f878baf12866e007ac4a13af19bbcabafda30)
