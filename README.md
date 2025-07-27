# Challenge GH Archive Keyword

## :rocket: THE project

This project aims to provide detailed statistics about GitHub public events (commit, pull-request, comments) related to a keyword. These data will be displayed on a daily basis.

![Screenshoot](./challenge-gh-keyword.png)

It uses [GH Archive](https://www.gharchive.org/) as its single source of truth. [GH Archive](https://www.gharchive.org/) records public GitHub data (commit, pull requests,...), archives it, and makes it easily accessible for further analysis.

## :woman_teacher: What you will have to do

### Implement events import functionality

:point_right: **Must have**

GitHub events will be imported by a Symfony command. Your goal is to fulfill this command in order to import GitHub events in our database. This command can be found here:

```
App\Command\ImportGitHubEventsCommand
```

API contract and entities are already done. But feel free to adapt the code as you like.

### Refactor the project

:point_right: **Nice to have**

This **code base does not fulfill** Yousign quality requirements.  
Feel free to adapt the code with your own standards, quality requirements and sensibilities but keep in mind the functional perimeter of the project.


## :nerd_face: Requirements

* Create a fork of this project
* **Must have**: Create a dedicated PR targeted on your forked project for the command  (**IMPORTANT**: PR should be targeted on YOUR forked repo and NOT our)
* **Nice to have**: Create a dedicated refactoring PR targeted on your forked project  (**IMPORTANT**: PR should be targeted on YOUR forked repo and NOT our)

Your PR will be reviewed during our technical interview, and it will be a support to talk about your choices.

## :computer: How to use development environment

You need `make` and `docker` installed to start the development environment.

### Start the development environment

The following command will start the development environment:

```bash
make start
```

You can access to the application at [127.0.0.1:8000](http://127.0.0.1:8000/).

### Access to a shell in the PHP container

```bash
make shell
```

### Test tools

You can run PHPUnit with the following command:
```bash
# Run the unit test suite
make unit-test

# init the db test (required once)
make db-test

# Run the functional test suite
make func-test
```

### Stop the development environment

You can stop the development environment running this command:
```bash
make stop
```

### Clean the development environment

You can clean the development environment (docker images, vendor, ...) running this command:
```bash
make clean
```

### Code Quality Tools

This project uses several code quality tools that run automatically via pre-commit hooks:

#### Pre-commit Hooks

The following tools run automatically before each commit:

- **PHP CS Fixer**: Enforces coding standards (PSR-12 + Symfony)
- **PHPStan**: Static analysis for type safety and potential bugs
- **Rector**: Code modernization and refactoring suggestions

#### Manual Usage

You can also run these tools manually:

```bash
# Run PHP CS Fixer
vendor/bin/php-cs-fixer fix

# Run PHPStan
vendor/bin/phpstan analyse

# Run Rector (dry-run to see what would change)
vendor/bin/rector process --dry-run

# Run Rector (apply changes)
vendor/bin/rector process
```

#### Pre-commit Setup

If you haven't set up pre-commit hooks yet:

```bash
# Install pre-commit
pip install pre-commit

# Install the git hooks
pre-commit install
```

### Makefile targets

You can get available targets by running:
```bash
make
```

```bash
build                          Build the docker stack
pull                           Pulling docker images
shell                          Enter in the PHP container
start                          Start the docker stack
stop                           Stop the docker stack
clean                          Clean the docker stack
vendor                         Install composer dependencies
unit-test                      Run PhpUnit unit testsuite
func-test                      Run PhpUnit functionnal testsuite
```
**We encourage you to have fun during this challenge!**

# :muscle: Good luck!
