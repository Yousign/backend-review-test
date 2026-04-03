# Challenge GH Archive Keyword

## The project

This project provides statistics about GitHub public events (commits, pull requests, comments) related to a keyword, displayed on a daily basis.

![Screenshot](./challenge-gh-keyword.png)

It uses [GH Archive](https://www.gharchive.org/) as its single source of truth. GH Archive records public GitHub activity, archives it, and makes it accessible for further analysis.

## How to use the development environment

You need `make` and `docker` installed.

### Start

```bash
make start
```

The application is available at [127.0.0.1:8000](http://127.0.0.1:8000/).

### Shell access

```bash
make shell
```

### Tests

```bash
# Run the unit test suite
make unit-test

# Init the test DB (required once)
make db-test

# Run the functional test suite
make func-test
```

### Stop / Clean

```bash
make stop
make clean
```

### All available targets

```bash
make
```

```
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

## Features we'd like to build next

Here are some directions we're considering for the future — we'll discuss these together during the interview:

- **GitHub events import** — complete the `ImportGitHubEventsCommand` to fetch and persist events from GH Archive
- **Pagination** — handle large result sets on the search endpoint
- **Advanced filtering** — filter by event type, actor, or date range
- **Caching** — reduce redundant queries for frequently accessed stats
- **Export** — allow downloading event data as CSV or JSON

## Interview format

This is a **technical discussion interview**, not a (live) coding test. Here's what to expect:

**Before the interview**
- Fork this repository and spend around **2 hours exploring the codebase** — understand how it works, its structure, what it does well, and what you would improve
- Feel free to use AI tools during your preparation, but don't use them to generate support material for the interview (see below)
- Come ready to discuss the code, its architecture, and how you'd approach the features above
- And again, no need to implement anything

**During the interview**
- You'll be discussing with **one developer and one engineering manager**, during **1 hour and a half**
- You'll be asked to **share your screen** and walk us through the code in your IDE while we dive on specific areas together
- We'll also do some **whiteboarding** together — please have a tool ready (e.g. [Draw.io](https://draw.io), [Miro](https://miro.com), Excalidraw, or any other live diagramming tool you're comfortable with)
- We're interested in your **reasoning and analytical thinking** as well as your **programming litteracy**, not in whether you've memorized the code
- Please **don't use any pre-prepared support material** into the interview (slides, notes etc.). We want to hear your own live thoughts, not a restitution of content that was potentially AI-generated
- **Using AI during the interview is not allowed** and will be considered disqualifying

## A note on AI and this process

We are strong advocates for bullish AI adoption in day-to-day engineering work. However with this interview we want to evaluate your technical thinking and reasoning, not your ability to prompt a model. We hope that makes sense, and we look forward to a genuine conversation.