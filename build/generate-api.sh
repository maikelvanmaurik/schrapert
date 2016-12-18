# Get ApiGen.phar
wget http://www.apigen.org/apigen.phar

# Generate Api
php apigen.phar generate -s ../src -d ../api --google-analytics="UA-25896402-2"
mkdir ../gh-pages
cd ../gh-pages

# Set identity
git config --global user.email "travis@travis-ci.org"
git config --global user.name "Travis"

# Add branch
git init
git remote add origin https://${GH_TOKEN}@github.com/maikelvanmaurik/schrapert.git > /dev/null
git checkout -B gh-pages

# Copy over the newly generated api
cp -Rf ../api api

# Push generated files
git add .
git commit -m "API updated"
git push origin gh-pages -q > /dev/null
