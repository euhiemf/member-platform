

pages = 
	'personal-number': ->
		@$el.html "<input type='text' placeholder='Enter your personnummer' style='width: 200px'><button>Go</button> or <a href='no-link' id='next'>skip</a>"
	'form': ->
		@$el.html "<form>this is a form! <a href='no-link' id='prev'>Back</a></form>"


pages_map = ['personal-number', 'form']
current_page = 0

next = ->
	if current_page < pages_map.length - 1
		current_page++
		update @

prev = ->
	if current_page > 0
		current_page--
		update @

update = (context) ->
	pages[pages_map[current_page]].call context


amd_define (req) ->

	class View extends Backbone.View
		events:
			'click #next': 'next'
			'click #prev': 'prev'


		initialize: ->
			@next = next.bind @
			@prev = prev.bind @


		render: ->
			update @


	return View
