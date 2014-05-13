# Courses

A course builder and delivery app for the [Elefant CMS](http://www.elefantcms.com/).
A free and simple way to publish and host your own courses that keeps you in control.

Here is a screenshot of the course editor:

![Course editor](https://raw.github.com/jbroadway/courses/master/pix/screenshot-editor.png)

## Features

* Publish your own courses of any length
* Easy-to-use and powerful course editor
* Embed SCORM modules and other dynamic content into courses
* Courses can be free, paid, or private
* Easy learner account management
* Learner input and instructor feedback cycle
* Built on a fast, completely modern CMS platform
* Easy theming of your learner website
* Integrate with the Courses API

## To do

Backend:

* [See our Github issues](https://github.com/jbroadway/courses/issues?state=open)

Email notifications:

* To instructor for new assessment input
* To instructor for new comments
* To instructor for new learner registered
* To learner welcome email

Documentation:

* How to use the Courses API

## Installation

First, you will need to install the [Elefant CMS](http://www.elefantcms.com/download).
Once that is running, follow these steps:

1\. From the root folder of the site run the following command:

```bash
php composer.phar require elefant/app-courses
```

This will also install the following apps that Courses depends on:

* [Comments](https://github.com/jbroadway/comments)
* [SCORM](https://github.com/jbroadway/scorm)
* [Stripe Payments](https://github.com/jbroadway/stripe)

> **Note:** You may need to add `"minimum-stability": "dev"` to your `composer.json`
> file in order for Composer to work correctly while Courses is still in development.

> **Payments:** Additional payment providers can be supported by implementing the
> [payment handler interface found here](https://github.com/jbroadway/stripe#creating-a-member-payment-or-subscription-form).
> More documentation and examples still to come.

2\. Log into Elefant and run the Courses installer by navigating to Tools > Courses.

3\. Add the following line to the `[Hooks]` section of `conf/config.php` to enable
email notifications of comments to course instructors:

```
comments/add[] = courses/hook/comments
```

4\. Go to Tools > Navigation and add the `Courses` page to your site tree.

You should now have a working installation.

## First steps

To create courses, go to Tools > Courses. To install SCORM modules for use in your
courses, go to Tools > SCORM. To view the list of courses on your site visit the
`/courses` URL and you will see any publicly visible courses listed there.

## Documentation

Documentation for the Courses app is [managed here](https://github.com/jbroadway/courses-docs).
A website for the documentation is coming soon.
