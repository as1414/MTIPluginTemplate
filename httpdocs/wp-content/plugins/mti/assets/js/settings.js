const forms = document.querySelectorAll('form.mti-options');
for (let i = 0; i < forms.length; i++) forms[i].addEventListener('submit', capture_blank);

/// add hidden input for each unchecked checkbox... this sends the value of 0 for unchecked ones which would normally not be included in the form posting
function capture_blank(event) {
	const form = event.target;
	const inputs = form.querySelectorAll('input[type=checkbox]:not(:checked)');

	for (let i = 0; i < inputs.length; i++) {
		const hiddenField = document.createElement('input');
		hiddenField.type = 'hidden';
		hiddenField.name = inputs[i].getAttribute('name');
		hiddenField.value = 0;

		form.appendChild(hiddenField);
		console.log(inputs[i]);
	}
}
