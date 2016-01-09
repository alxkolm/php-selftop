import 'angular';
import 'angular-route';
import {Route} from './app.route';

angular
    .module('app', ['ngRoute'])
    .config(['$routeProvider', Route]);