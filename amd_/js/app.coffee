
amd_requirejs.config
	baseUrl: '/amd_',
	paths:
		config: '/config.json'
		router: '/router.json'
		text: 'lib/text'
		coffee: 'lib/cs'
		apps: '/apps_'

window.underscore = window._ = _ = require 'underscore'
window.Backbone = Backbone = require 'backbone'
window.jQuery = window.$ = Backbone.$ = require 'jquery'

require '../lib/backbone-deep-model.min.js'

require 'bootstrap'

require '../lib/metris.jquery.js'



$(window).bind "load resize", () ->
    if $(@).width() < 768
        $('div.sidebar-collapse').addClass('collapse')
    else
        $('div.sidebar-collapse').removeClass('collapse')





Links = require './links.coffee'


amd_requirejs ['text!router', 'text!config'], (str_router, str_config) ->
	# console.log str_r

	links = new Links JSON.parse(str_router)

	# x = JSON.parse str_router
	# console.log x

	links.route()
	links.buildMenu()



amd_requirejs ['coffee!apps/register/app'], (mod) ->

	mod()
