#!/bin/bash
# Script to quickly rename sub-theme.

echo '
   __  __     __                   ____           _       __    __
  / / / /____/ /_  ____ _____     /  _/___  _____(_)___ _/ /_  / /_
 / / / / ___/ __ \/ __ `/ __ \    / // __ \/ ___/ / __ `/ __ \/ __/
/ /_/ / /  / /_/ / /_/ / / / /  _/ // / / (__  ) / /_/ / / / / /_
\____/_/  /_.___/\__,_/_/ /_/  /___/_/ /_/____/_/\__, /_/ /_/\__/
                                                /____/
▒█▀▀▀█ ▀▀█▀▀ █▀▀█ █▀▀█ ▀▀█▀▀ █▀▀ █▀▀█ 　 ▀▀█▀▀ █░░█ █▀▀ █▀▄▀█ █▀▀
░▀▀▀▄▄ ░░█░░ █▄▄█ █▄▄▀ ░░█░░ █▀▀ █▄▄▀ 　 ░▒█░░ █▀▀█ █▀▀ █░▀░█ █▀▀
▒█▄▄▄█ ░░▀░░ ▀░░▀ ▀░▀▀ ░░▀░░ ▀▀▀ ▀░▀▀ 　 ░░▀░░ ▀░░▀ ▀▀▀ ▀░░░▀ ▀▀▀

+------------------------------------------------------------------------+
| With this script you can re-name bootstrap_ui                          |
| The new theme will be generated in the /custom folder                  |
+------------------------------------------------------------------------+
'
echo 'The machine name of your custom theme? [e.g. my_custom_name]'
read CUSTOM_BARRIO

echo 'Your theme name ? [e.g. My custom name]'
read CUSTOM_BARRIO_NAME

cd ../..
rsync -aq --progress bootstrap_ui/ $CUSTOM_BARRIO --exclude node_modules --exclude scripts
cd $CUSTOM_BARRIO
for file in *bootstrap_ui.*; do mv $file ${file//bootstrap_ui/$CUSTOM_BARRIO}; done
for file in config/*/*bootstrap_ui.*; do mv $file ${file//bootstrap_ui/$CUSTOM_BARRIO}; done
# mv {_,}$CUSTOM_BARRIO.theme
# mv {_,}$CUSTOM_BARRIO.layouts.yml
grep -Rl bootstrap_ui .|xargs sed -i '' -e "s/bootstrap_ui/$CUSTOM_BARRIO/"
sed -i -e "s/Bootstrap UI/$CUSTOM_BARRIO_NAME/" $CUSTOM_BARRIO.info.yml
echo "# Check the themes/custom folder for your new sub-theme."
