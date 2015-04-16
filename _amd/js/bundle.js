(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
requirejs.config({
  baseUrl: '_amd',
  paths: {
    config: '../config.json',
    router: '../router.json',
    text: 'lib/text'
  }
});

requirejs(['text!router'], function(str_r) {
  return console.log(str_r);
});



},{}]},{},[1])
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIm5vZGVfbW9kdWxlcy93YXRjaGlmeS9ub2RlX21vZHVsZXMvYnJvd3NlcmlmeS9ub2RlX21vZHVsZXMvYnJvd3Nlci1wYWNrL19wcmVsdWRlLmpzIiwiQzpcXFVzZXJzXFxldWhpZW1mXFxEb2N1bWVudHNcXEdpdEh1YlxcbWVtYmVyLXBsYXRmb3JtXFxfYW1kXFxqc1xcYXBwLmNvZmZlZSJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0VBLFNBQVMsQ0FBQyxNQUFWLENBQ0M7QUFBQSxFQUFBLE9BQUEsRUFBUyxNQUFUO0FBQUEsRUFDQSxLQUFBLEVBQ0M7QUFBQSxJQUFBLE1BQUEsRUFBUSxnQkFBUjtBQUFBLElBQ0EsTUFBQSxFQUFRLGdCQURSO0FBQUEsSUFFQSxJQUFBLEVBQU0sVUFGTjtHQUZEO0NBREQsQ0FBQSxDQUFBOztBQUFBLFNBUUEsQ0FBVSxDQUFDLGFBQUQsQ0FBVixFQUEyQixTQUFDLEtBQUQsR0FBQTtTQUMxQixPQUFPLENBQUMsR0FBUixDQUFZLEtBQVosRUFEMEI7QUFBQSxDQUEzQixDQVJBLENBQUEiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwiXHJcblxyXG5yZXF1aXJlanMuY29uZmlnXHJcblx0YmFzZVVybDogJ19hbWQnLFxyXG5cdHBhdGhzOlxyXG5cdFx0Y29uZmlnOiAnLi4vY29uZmlnLmpzb24nXHJcblx0XHRyb3V0ZXI6ICcuLi9yb3V0ZXIuanNvbidcclxuXHRcdHRleHQ6ICdsaWIvdGV4dCdcclxuXHJcblxyXG5yZXF1aXJlanMgWyd0ZXh0IXJvdXRlciddLCAoc3RyX3IpIC0+XHJcblx0Y29uc29sZS5sb2cgc3RyX3JcclxuXHJcbiJdfQ==
