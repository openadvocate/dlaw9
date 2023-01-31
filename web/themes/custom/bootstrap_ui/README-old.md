# Gulp Bootstrap 4 - Barrio SASS Starter Kit

A starter kit utilizing Bootstrap 4 with support for a component based architecture/workflow.
This starter kit utilizes bootstrap_barrio as the parent theme and requires the same initial setup (adding the Bootstrap source files), BrowserSync is enabled by default, but the task runner can be used without it. The components in the scss/components directory can be modified directly, but our recommendation is that that overrides are handled in the custom folder.

## Requirements
* npm or yarn (to take care of sass compilation)
* Bootstrap Barrio theme (see initial setup)
* You must use the [Bootstrap Framework Source Files] ending in the `.scss`
    extension, not files ending in `.css`.

## Initial setup

###Node and NVM
Be sure you have a recent version of node installed. We recommend using Node version manager or NVM, so that it's easier to quickly switch between versions as needed. It's best to stick with one version for running Gulp, so don't install the task runner with version 10 and then later launch it with version 8 and expect it to work.

Example command
```x
$ nvm use stable
```

Show installed version of node
```
$ nvm ls
```

Show remotely available versions of node
```
$ nvm ls-remote
```

Use specific version of node
```
$ nvm use 12.14.1
```

Switch default version loaded with terminal session
```
nvm alias default 12.14.1
```

###NPM
Make sure you have npm installed on your machine, or [yarn](https://yarnpkg.com/en/) which is a package manager that is becoming increasingly popular because of superior performance and numerous conveniences.

From this theme's directory:

`npm install` or `yarn`

###Gulp
If you want to use gulp via command line best to install it globally:

`npm install --global gulp-cli`

##Theme Structure
```
/gulp-tasks             // modularized gulp tasks (do not need to touch unless you want to add tasks)

/scss                   // write all of your custom components and global js/less here

  /custom             // custom components in their own directory
    /components
      _component.scss
        component.md

/templates                     // drupal twig template overrides

  /components
    /component
      paragraph--hero-component.html.twig

/js
  /components
    component.js


gulp.settings.js               // read the intstructions in this file
gulpfile.js                    // contains the watch task and imports all of the modularized gulp tasks
bootstrap_ui.libraries.yml     // default libraries include bootstrap, global css and js
bootstrap_ui.info.yml          // default theme info file including regions and global libraries
bootstrap_ui.theme             // theme functions


```

##Yaml
Define a library for your custom component and include it in the theme yaml file. This is mainly for loading JS, as Sass is handled separately.
```
global-styling:
  version: VERSION
  js:
    js/popper.min.js: {}
    js/bootstrap.min.js: {}
    js/global.js: {}
    js/fontawesome/all.min.js: {}
  css:
    component:
      css/style.css: {}
  dependencies:
    - core/jquery
    - core/drupal
    - core/jquery.once

component-name:
  version: 1.0
  js:
    js/components/component.js: {}
  dependencies:
    - core/jquery
    - core/jquery.once
```

## Gulp Tasks
`gulp` - clean and compile sass, move js to destination folder, auto update changes at `localhost:port`

`gulp watch-basic` - all of the above but no auto updates

## Component Structure (Depends on drupal components module: https://www.drupal.org/project/components)
Each component created needs:
1. Dedicated directory in `components` with twig file and less/js files if applicable
2. A library for the component defined in `bootstrap.libraries.yml`
3. The library needs to be attached in the component twig file

## Sass Styles
If you are interested in BEM styles and how they can apply to less check [here](https://bitbucket.org/snippets/urban-insight/ke6G7L/bem-styleguide)
