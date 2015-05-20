
request = (url, crud, pass, data, cb) ->

	headers =
		auth_identity: "admin"
		auth_secret: pass

	data = _.extend headers, data
	payload = JSON.stringify data

	headers = _.extend headers, { payload: payload }

	switch crud
		when 'CREATE' then method = 'POST'
		when 'READ' then method = 'GET'
		when 'UPDATE' then method = 'PUT'
		when 'DELETE' then method = 'DELETE'
	

	$.ajax
		type: 'POST'
		url: url
		# headers:
		# 	'Content-Type': 'application/x-www-form-urlencoded'
		beforeSend: (request) =>
			request.setRequestHeader('X-HTTP-Method-Override', method);

		dataType: 'json'
		data: payload
		headers: headers

		success: cb



amd_define ['text!./html/start.html', 'text!./html/form.html', 'text!./html/cam.html', 'text!./html/done.html', './es6-promise', './webcam'], (templates..., Promise, Webcam) ->

	Promise = Promise.Promise

	checkPassword = (password) -> new Promise((resolve, reject) ->

		url = 'http://killergame.nu/members2/user/noobtoothfairy@gmail.com'

		if location.hostname is 'localhost'
			url = 'http://localhost/memberdev/user/noobtoothfairy@gmail.com'

		request url, 'READ', password, {}, (data) -> (
			if data?.email is 'noobtoothfairy@gmail.com' then resolve() else reject(data)
		)
	)

	getAutofill = (nin) -> new Promise((resolve, reject) ->
		settings = 
			url: 'http://killergame.nu/members/index.php?action=get_autofill&q=' + nin
			type: "GET",
			dataType: 'json'

		$.ajax(settings).done(resolve).always(reject)

	)


	class View extends Backbone.View

		events:
			'click #next': 'next'
			'click #prev': 'prev'
			'submit form': (ev) -> ev.preventDefault()
			'submit form#start': (ev) ->
				form = $(ev.currentTarget)
				@renderForm form
			'submit form#form': ->
				@saveForm()
				@next()

			'submit form#selfie': ->
				# create
				# request(url, crud, pass, data, cb)

				@next()

				base_url = 'http://killergame.nu/members2/user/' + @form_data['user_email']
				NProgress.start()

				tht = @

				sp = (step) ->
					NProgress.set 0.25 * step
					tht.$("#progress").html step


				request(base_url, 'CREATE', tht.pass, {}, ->
					sp 1
					request(base_url + '/card', 'CREATE', tht.pass, {card_number: tht.code}, ->
						sp 2
						request(base_url + '/credentials', 'CREATE', tht.pass, tht.form_data, ->
							sp 3
							request(base_url + '/image', 'CREATE', tht.pass, {image: tht.selfie_url}, ->
								sp 4
								setTimeout((->
									tht.$('.loading').hide()
									tht.$('.finish').show()
									NProgress.done()
								), 750)
							)
						)
					)
				)


			'click #webcam': ->

				Webcam.snap ((data_uri) ->

					@selfie_url = data_uri

					@$('#result img').attr('src', data_uri)
					@$('#webcam-container').hide()
					@$('#result-container').show()

				).bind @

			'click #result': ->

				@$('#webcam-container').show()
				@$('#result-container').hide()


		consts: ->
			@current_page = 0
			@selfie_url = ""
			@code = ""
			@form_data = {}

		initialize: ->

			@delegateEvents()
			@consts()


			@once 'render', @render, @

			@on 'render:1', ->

				@$('#input_card_number').val @code

				if @autocomplete_data
					@autofill @autocomplete_data

			@on 'render:2', ->
				Webcam.attach @$('#webcam').get(0)
				video_el = @$('#webcam video').get(0)

				$(video_el).on 'playing', (->

					whr = video_el.videoWidth / video_el.videoHeight

					w = $('#webcam').width()
					h = w / whr

					@$(video_el).width(w).height(h).css({width: w + 'px', height: h + 'px'})
					@$('#webcam').width(w).height(h).css({width: w + 'px', height: h + 'px'})
					@$('#result').width(w).height(h).css({width: w + 'px', height: h + 'px'})


					Webcam.set
						width: w
						height: h
						dest_width: w
						dest_height: h

				).bind @



		saveForm: ->
			for key, ob_val of @mappings
				el = @$("##{key}")
				val = el.val()
				name = el.attr('name')
				@form_data[name] = val
				@autocomplete_data[ob_val] = val

			@code = @$("#input_card_number").val()

		mappings: 
			"input_sd_mobile_number": "Annattelefonnummer"
			"input_co_address": "Co-Adress"
			"input_login_email": "E-Post"
			"input_last_name": "Efternamn"
			"input_first_name": "Förnamn"
			"input_street_address": "Gatuadress"
			"input_class": "Klass"
			"input_sex": "Kön"
			"input_mobile_number": "Mobilnr."
			"input_post_town": "Ort"
			"input_nin": "Personnr"
			"input_postal_number": "Postnummer"

		autofill: (what)->

			for key, val of @mappings
				@$("#" + key).val what[val]



		setAutoCompleteData: (data) =>
			@autocomplete_data = {}
			for key, val of data
				key = key.replace(/\s|\r|\n/mg, '')
				@autocomplete_data[key] = val


		renderForm: (form) ->
			pass = form.find('#passphrase').val()
			nin = form.find('#personal-security-number').val().replace(/\s|\-/mg, '')
			if nin.length > 10
				nin = nin.slice 2

			NProgress.start()
			done = =>
				@next()
				@pass = pass
				NProgress.done()


			getNINdata = =>
				NProgress.set(0.5)
				getAutofill(nin).then(((data) =>
					@setAutoCompleteData(data);
					done()
				).bind(@)).catch(=>
					done()
				)


			checkPassword(pass).then(=> if nin.length then getNINdata() else done() ).catch(->
				NProgress.done()
				alert 'password error!'
				console.log arguments
			)





		next: ->
			@current_page++
			@update()
		prev: ->
			@current_page--
			@update()

		update: ->
			@$el.html templates[@current_page]
			@trigger "render:#{@current_page}"

			try document.forms[0].querySelector('input').focus()
			


		render: (code) ->
			@code = if code then code else ""
			@update()


	return View
