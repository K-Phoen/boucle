# Boucle ![PHP7 ready](https://img.shields.io/badge/PHP7-ready-green.svg) [![Build Status](https://travis-ci.org/K-Phoen/boucle.svg?branch=master)](https://travis-ci.org/K-Phoen/boucle) [![Coverage Status](https://coveralls.io/repos/github/K-Phoen/boucle/badge.svg?branch=master)](https://coveralls.io/github/K-Phoen/boucle?branch=master)

*Boucle* is a static travel map generator.

As your explored the world during your travels, *Boucle* will help you
build a map of your periples.

[See the demo](http://blog.kevingomez.fr/boucle/)

## Features

* Generation of a static map with all the steps of your travel
* Configuration through a simple YAML file
* Generation of static galleries, one for each of your travel steps
* Automatic generation of thumbnails
* Can be deployed on [GitHub's gh-pages](https://pages.github.com/)

## Usage

To generate your website, there are a few steps to follow:

1. [Download Boucle](#downloading-boucle)
2. [Describe your travel](#describing-the-travel)
3. [Generate the website](#generating-the-website)
4. [Deploy!](#deploying)

### Downloading Boucle

The easiest way to use the PHAR archive. Download the latest archive in the [releases page](https://github.com/K-Phoen/boucle/releases/)
and you will be good to go.

### Describing the travel

Boucle expects your travel to be described in a YAML file that looks like this:

```yaml
boucle:
    title: Scotland – 2018

    start: Clermont-Ferrand, France

    steps:
        -
            to: Lyon, France
            with: bus
            date: '2018-08-28'
        -
            to: Edinburgh, Scotland
            with: plane
            date: '2018-08-31'
            album:
                path: albums/edinburgh
                cover: P9020042.JPG
        -
            to: Stirling, Scotland
            with: car
            date: '2018-09-12'
            type: daytrip
        -
            to: Glasgow, Scotland
            with: car
            date: '2018-09-13'
        -
            to: Lyon, France
            with: plane
            date: '2018-09-15'
```

To check how Boucle understood your travel, you can use the following command:

```bash
./boucle steps:list
```

A more detailed example can be found in the [`examples`](./examples/boucle.yaml) directory.

### Generating the website

Once your albums are configured, you can use Boucle to generate the website for your:

```
./boucle build
```

### Deploying

If you are deploying on [GitHub's gh-pages](https://pages.github.com/), you can commit the
changes and push them.

## Authors

* **Kévin Gomez** - *Initial work*
* **Bjorn Sandvik** - *For the original version of the [Leaflet.Photo](https://github.com/turban/Leaflet.Photo) plugin*

See also the list of [contributors](https://github.com/K-Phoen/boucle/graphs/contributors) who participated in this project.

## Contributing

See the [CONTRIBUTING.md](CONTRIBUTING.md) file for details.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
