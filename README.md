# traphic

This repo is an extension to [arcanist](https://github.com/phacility/arcanist) to enable Travis-CI
integration to run [Phabricator](http://phabricator.org/) diffs through continuous integration. It
is mostly here as a reference. Please fork if you think all or parts of it may be useful to you.

## arcanist hooks

The two things it currently does is add post-diff and post-land hook. The post-diff hook pushes
the diff as a remote branch to GitHub so, when Travis-CI sees it, it will be built. The post-land
hook removes the remote branch after landing.

The way arcanist knows to use this library is the .arcconfig in the base dir of this project.
Specifically, this part:

```
"load" : [
  "path/to/conphig"
],
"arcanist_configuration" : "Conphig"

```
I think there's also a way to add these settings to your `~/.arcrc` so it can be cross-project.

If you're adding new classes, let arcanist know what to look for:
```
cd conphig
arc liberate
```
See also:
https://secure.phabricator.com/book/phabricator/article/libraries/
https://secure.phabricator.com/book/phabricator/article/arcanist_lint_unit/
https://secure.phabricator.com/book/arcanist/class/ArcanistConfiguration

## phabricator settings

Change Diffusion settings to track only branches that are not your diff branches, 
e.g. set *Track Only* to `regexp(/^(?!ES\_D)/)` and *Autoclose Only* to `master` 
or whatever branches you want to autoclose. Otherwise, it will autoclose diffs on 
your remote diff branches when they appear on the remote and are loaded by Phabricator.

You may also want to refactor this to make the `ES\_` prefix for the branches something
else. This was a convenient prefix for us.

## extra bonus scripts

For our integration, we also added a script that can be run on Travis to add a message to the diff
when the build succeeds or fails. We created a bot user in our Phabricator instance to have
the credentials to do this.

This, unfortunately, means you need to install PHP and other things
every time you do a CI. Here is some `travis.yml` config for your approbation:
```
env:
  global:
    - JAVA_OPTS="-Xms2000m -Xmx4000m -XX:MaxPermSize=512M"
    - PATH="$PATH:./path/to/phaceWrapScript"

install:
  - sudo apt-get install php5 php5-curl jq
  - git clone git://github.com/facebook/libphutil.git
  - git clone git://github.com/facebook/arcanist.git
  # And somehow either have phaceWrap in your repo or install it separately

before_script:
  - phaceWrap "starting"

after_failure:
  - phaceWrap "failure"

after_success:
  - phaceWrap "success"
```
In hindsight, I should have written these scripts in PHP instead of Bash, but such is life.

The `.traphic` script in the base dir of your git repo should have some settings like
how to find the conduit credentials and where you're Phabricator install is. See
`scripts/traphic.sample`.

The `phobot.arcrc` file looks something like this:
```
{
  "hosts": {
    "https:\/\/your.phabricator.com\/api\/": {
      "token": "cli-somelongtoken"
    }
  }
}
```
It can be created by hand, but you need to get the conduit api token for your bot (or other) user
to create it. Usually this is done by `arc install-certificate` and following the instructions.

## caveats

I have this working, but YMMV. The scripts in this configuration have not been tested, since
I did some refactoring to make this repo public. Hopefully I'll update with fixes. I can
answer some questions, but mostly this was built by reading a lot of Phrabricator PHP
code and figuring it out. I'm not generally a PHP developer, but I play one on TV.
