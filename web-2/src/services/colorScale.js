import 'angular';
var colorbrewer =  require('../libs/colorbrewer').colorbrewer;

function ColorScale(){
    return d3.scale.ordinal().range(colorbrewer.Set1[9]);
}

angular
    .module('app')
    .factory('ColorScale', ColorScale);