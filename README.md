## Install

### Install prerequisites
    
- Install https://github.com/alxkolm/go-selftop
- Install php5 sqlite module

    `sudo apt-get install php5-sqlite`



### Install
    
    git clone https://github.com/alxkolm/php-selftop.git
    cd php-selftop
    ln -s ~/.selftop/selftop.db selftop.db
    ./yii migrate
    
## Run

    cd php-selftop/web
    php -S localhost:8000

