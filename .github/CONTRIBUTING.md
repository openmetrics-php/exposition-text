# Contributing

Contributions are **welcome** and will be fully **credited**.

We accept contributions via pull requests on [GitHub](https://github.com/openmetrics-php/exposition-text).

## Pull Requests

- **Add tests!** - Your patch will not be accepted if it does not have tests.

- **Document any change in behaviour** - Make sure the documentation in `README.md` is kept up-to-date.

- **Consider our release cycle** - We follow [SemVer v2.0.0](http://semver.org/). Randomly breaking public APIs is not an option.

- **Create topic branches** - Do not ask us to pull from your master branch.

- **One pull request per feature** - If you want to do more than one thing, please send multiple pull requests.

- **Send coherent history** - Make sure each individual commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please squash them before submitting.

### Install development environment

```bash
make install
```

### Run PHPStan

```bash
make phpstan
```

### Run PHP-version specific tests

```bash
make test-php-7.2
make test-php-7.3
make test-php-7.4
make test-php-8.0
make test-php-8.1
make test-php-8.2
make test-php-8.3
```

### Run all tests

```bash
make tests
```
