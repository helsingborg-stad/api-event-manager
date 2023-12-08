<!-- SHIELDS -->
[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![License][license-shield]][license-url]
![PHP Version](https://img.shields.io/badge/PHP->=8.1-blue)
![Unit Tests](https://github.com/helsingborg-stad/api-event-manager/actions/workflows/php-test.yaml/badge.svg)


# API Event Manager

  A WordPress plugin that creates a api that may be used to manage events.
  
  [Report Bug](https://github.com/helsingborg-stad/api-event-manager/issues)
  ·
  [Request Feature](https://github.com/helsingborg-stad/api-event-manager/issues)

## About API Event Manager

### Built With

* PHP
* WordPress

## Getting Started

To get a local copy up and running follow these simple steps.

### Development environment (VS Code Devcontainer)

#### Prerequisites

* Docker
* VS Code

#### Installation of development environment.

1. Install VS Code.
1. Install the extension [Remote Development](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.1scode-remote-extensionpack).
1. Clone this repo.
1. Open the folder in VS Code.
1. Open the command palette (cmd + shift + p).
1. Type `Remote-Containers: Reopen in Container` and select this alternative.
1. Wait for the container to build.
1. Run composer install in the VS Code integrated terminal.
1. Run the provided VS Code Task `install acf pro`. This will download and install ACF Pro, which is required for this WordPress plugin to work.

## Tests

### Run tests
Run `composer test` in the terminal.

## Constants

## Contributing

Contributions are what make the open source community such an amazing place to be learn, inspire, and create. Any contributions you make are **greatly appreciated**.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

Distributed under the [MIT License][license-url].

## Acknowledgements

- [othneildrew Best README Template](https://github.com/othneildrew/Best-README-Template)


<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/helsingborg-stad/api-event-manager
[contributors-url]: https://github.com/helsingborg-stad/api-event-manager/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/helsingborg-stad/api-event-manager.svg?style=flat-square
[forks-url]: https://github.com/helsingborg-stad/api-event-manager/network/members
[stars-shield]: https://img.shields.io/github/stars/helsingborg-stad/api-event-manager.svg?style=flat-square
[stars-url]: https://github.com/helsingborg-stad/api-event-manager/stargazers
[issues-shield]: https://img.shields.io/github/issues/helsingborg-stad/api-event-manager.svg?style=flat-square
[issues-url]: https://github.com/helsingborg-stad/api-event-manager/issues
[license-shield]: https://img.shields.io/github/license/helsingborg-stad/api-event-manager.svg?style=flat-square
[license-url]: https://github.com/helsingborg-stad/api-event-manager/blob/main/LICENSE
