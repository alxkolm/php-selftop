FROM phusion/baseimage:0.9.15

# Ensure UTF-8
RUN locale-gen en_US.UTF-8
ENV LANG       en_US.UTF-8
ENV LC_ALL     en_US.UTF-8

ENV HOME /root

RUN /etc/my_init.d/00_regen_ssh_host_keys.sh

CMD ["/sbin/my_init"]

# Nginx-PHP Installation
RUN apt-get update
RUN DEBIAN_FRONTEND="noninteractive" apt-get install -y vim curl wget build-essential python-software-properties
RUN add-apt-repository -y ppa:ondrej/php5
RUN add-apt-repository -y ppa:nginx/stable
RUN apt-get update
RUN DEBIAN_FRONTEND="noninteractive" apt-get install -y --force-yes php5-cli php5-fpm php5-sqlite php5-curl

RUN sed -i "s/;date.timezone =.*/date.timezone = UTC/" /etc/php5/fpm/php.ini
RUN sed -i "s/;date.timezone =.*/date.timezone = UTC/" /etc/php5/cli/php.ini

RUN DEBIAN_FRONTEND="noninteractive" apt-get install -y nginx

RUN echo "daemon off;" >> /etc/nginx/nginx.conf
RUN sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php5/fpm/php-fpm.conf
RUN sed -i "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/" /etc/php5/fpm/php.ini
 
RUN mkdir -p        /var/www
ADD build/default   /etc/nginx/sites-available/default
RUN mkdir           /etc/service/nginx
ADD build/nginx.sh  /etc/service/nginx/run
RUN chmod +x        /etc/service/nginx/run
RUN mkdir           /etc/service/phpfpm
ADD build/phpfpm.sh /etc/service/phpfpm/run
RUN chmod +x        /etc/service/phpfpm/run

EXPOSE 80
# End Nginx-PHP

# Python, NumPy, SciPy, Scikit Learn
RUN apt-get install -y \
    build-essential \
    python-dev \
    python-setuptools \
    gfortran \
    libblas-dev \
    libatlas-dev \
    liblapack-dev

RUN easy_install pip \
    && pip install --upgrade distribute \
    && pip install numpy==1.7.1 \
    && pip install scipy==0.12.0 \
    && pip install -U scikit-learn

# Install MCL
RUN mkdir -p /root/mcl-src
WORKDIR /root/mcl-src
RUN wget http://micans.org/mcl/src/mcl-14-137.tar.gz \
   && tar xzf mcl-14-137.tar.gz \
   && cd mcl-14-137 \
   && ./configure \
   && make install

RUN apt-get purge -y \
    build-essential \
    python-dev \
    libblas-dev \
    libatlas-dev \
    liblapack-dev

RUN mkdir /selftop

RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
