Before use this software you need install and run this   
 
- [xrecord-echo](https://github.com/alxkolm/rust-xrecord-echo)
- [go-selftop](https://github.com/alxkolm/go-selftop)

# Install

The easiest way to use this software is run docker image
    
    cd docker
    sudo docker-compose up
    
And then open ```localhost:8080``` in browser

## Manual install
### Install prerequisites
- Install php5 sqlite module

    `sudo apt-get install php5-sqlite`
    
- Install [scikit-learn](http://scikit-learn.org/stable/install.html)
- Install [Markov Cluster Algorithm](http://micans.org/mcl/). It's fairly hard to find link to source. See "License & software" section.

### Install
    
    git clone https://github.com/alxkolm/php-selftop.git
    cd php-selftop
    sudo ln -s ~/.selftop /selftop
    ./yii migrate
    
## Run

    cd php-selftop/web
    php -S localhost:8000

