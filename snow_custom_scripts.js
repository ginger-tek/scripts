window.onload = () => {
	setTimeout(() => {
		console.log('Running scripts');
		addCalBtn();
		ctrlSave();
		preloadChangeCom();
		readonlyNumberField();
	}, 1);
};

function addCalBtn() {
	const changeNum = document.getElementById('change_request.number');
	if (!changeNum) return;
	console.info('[ChangeNum]', changeNum.value);
	const btn = document.createElement('button');
	btn.type = 'button';
	btn.width = 'auto';
	btn.classList.add('btn', 'btn-default');
	btn.innerText = 'Add to Calendar';
	btn.addEventListener('click', () => {
		const calLink = createOutlookCalLink({
			sysId: document.getElementById('sys_uniqueValue').value,
			number: changeNum.value,
			description: document.getElementById('change_request.description').value,
			summary: document.getElementById('change_request.short_description')
				.value,
			startDate: document.getElementById('change_request.start_date').value,
			endDate: document.getElementById('change_request.end_date').value,
		});
		if (!calLink) {
			console.error('Failed to create calendar link');
			return;
		}
		window.open(calLink);
	});
	const wrapSpan = document.createElement('span');
	wrapSpan.appendChild(btn);
	const btnWrap = document.createElement('div');
	btnWrap.classList.add(
		'col-xs-2',
		'col-sm-3',
		'col-lg-2',
		'form-field-addons'
	);
	btnWrap.appendChild(wrapSpan);
	changeNum.parentElement.parentElement.appendChild(btnWrap);
}

function createOutlookCalLink(data) {
	if (!data.number || !data.summary || !data.startDate || !data.endDate)
		return null;
	const url = `${location.origin}/nav_to.do?uri=change_request.do?sys_id=${data.sysId}`;
	const params = new URLSearchParams();
	params.set('allday', 'false');
	params.set(
		'body',
		`Link: <a href="${url}">${data.number}</a><br>${data.description.replace('\r\n', '<br>')}`
	);
	params.set('startdt', new Date(data.startDate).toISOString());
	params.set('enddt', new Date(data.endDate).toISOString());
	params.set('path', '/calendar/action/compose');
	params.set('rru', 'addevent');
	params.set('subject', `${data.number}: ${data.summary}`);
	return `https://outlook.office365.com/calendar/0/action/compose?${params.toString()}`;
}

function ctrlSave() {
	window.addEventListener('keydown', (e) => {
		if (e.ctrlKey && e.key == 's') {
			e.preventDefault();
			e.target?.blur();
			setTimeout(() => {
				console.info('Saving record');
				if (gsftSubmit) gsftSubmit(gel('sysverb_update_and_stay'));
				else console.warn('gsftSubmit not defined, iframe not in focus');
			});
		}
	});
}

function preloadChangeCom() {
	const textbox = document.getElementById(
		'change_request.u_communication_plan'
	);
	const com = localStorage.getItem('change_com');
	if (textbox && !textbox.value && com) {
		textbox.focus();
		textbox.value = com;
		textbox.dispatchEvent(new Event('input', { bubbles: true }));
		textbox.blur();
	}
}

function readonlyNumberField() {
	const field = document.getElementById('incident.number') ||
		document.getElementById('change_request.number') || false;
	if (field)
		field.readOnly = true;
}
