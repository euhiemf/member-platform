

requirejs.config
	baseUrl: '_amd',
	paths:
		config: '../config.json'
		router: '../router.json'
		text: 'lib/text'


requirejs ['text!router'], (str_r) ->
	console.log str_r

