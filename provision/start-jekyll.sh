cd /vagrant

jekyll=$(which jekyll)
log="/home/vagrant/jekyll.log"
run="sudo bundler exec $jekyll serve --host 0.0.0.0 --port 80 --source /vagrant --destination /home/vagrant/_site --baseurl='' --watch --force_polling >> $log 2>&1 &"
eval $run

