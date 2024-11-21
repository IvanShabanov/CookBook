/* В календаре в наименовании события указывается в скобках номер задачи  */
const IS_Bitrix24 = {
	Config: {
		CalendarRegTask: /.*\((\d+)\).*/,
		CalendarRegTaskArrayResult: 1,
		OpenLinksNewWindow: false,
	},
	setTumeoutID: 0,
	AutoRun() {
		IS_Bitrix24.Run();
		document.addEventListener('click', () => {
			clearTimeout(IS_Bitrix24.setTumeoutID);
			IS_Bitrix24.setTumeoutID = setTimeout(() => {
				IS_Bitrix24.Run();
			}, 1000);
		});
		setInterval(() => {
			IS_Bitrix24.Run();
		}, 5000);
	},
	Run() {
		let loc = window.location.href;
		if (loc.includes('/calendar/')) {
			IS_Bitrix24.Calendar.AddLinkToTaskOnList();
			IS_Bitrix24.Calendar.AddLinkToTaskOnPopup();
			IS_Bitrix24.Calendar.HighlightEventsByGet();
		}
		if (loc.includes('/task/view/')) {
			IS_Bitrix24.Task.AddLinkTaskCalendar();
		}
	},
	User: {
		getIDFromUrl() {
			let user = window.location.href.match(/user\/(\d+)\//);
			if (typeof user[1] == 'undefined' || !user[1]) {
				return -1;
			}
			return user[1];
		}
	},
	Calendar: {
		HighlightEventsByGet() {
			let url = new URL(window.location.href);
			var taskID = url.searchParams.get("hltaskid");
			if (typeof taskID == 'undefined' || !taskID) {
				return;
			}
			IS_Bitrix24.Calendar.HighlightEventsByText('(' + taskID + ')', '#aa0000');
		},
		HighlightEventsByText(searchTitleText, backgroundColor) {
			let CalendarEnevts = document.querySelectorAll('.calendar-event-block-inner')
			if (!CalendarEnevts) {
				return;
			}
			CalendarEnevts.forEach((CalendarEvent) => {
				let title = CalendarEvent.querySelector('.calendar-event-block-title .calendar-event-block-text');
				if (title.textContent.includes(searchTitleText)) {
					CalendarEvent.style.background = backgroundColor;
				}
			});
		},
		AddLinkToTaskOnList(regTask, whereIDtask) {
			regTask = typeof regTask !== 'undefined' ? regTask : IS_Bitrix24.Config.CalendarRegTask;
			whereIDtask = typeof whereIDtask !== 'undefined' ? whereIDtask : IS_Bitrix24.Config.CalendarRegTaskArrayResult;
			let CalendarEnevts = document.querySelectorAll('.calendar-event-block-inner')
			if (!CalendarEnevts) {
				return;
			}
			let userID = IS_Bitrix24.User.getIDFromUrl();
			if (!userID) {
				return;
			};

			CalendarEnevts.forEach((CalendarEvent) => {
				let title = CalendarEvent.querySelector('.calendar-event-block-title .calendar-event-block-text');
				let allreadyLink = title.querySelector('a');
				if (!!allreadyLink) {
					return;
				}
				let titleParse = title.textContent.match(regTask);
				if (!titleParse) {
					return;
				}
				if (typeof titleParse[whereIDtask] == 'undefined' || typeof titleParse[whereIDtask] == 'NaN' || !titleParse[whereIDtask]) {
					return;
				}
				let link = IS_Bitrix24.Task.createLink(userID, titleParse[whereIDtask]);
				link.textContent = ' [Задача]';
				title.appendChild(link);
			});

		},
		AddLinkToTaskOnPopup(regTask, whereIDtask) {
			regTask = typeof regTask !== 'undefined' ? regTask : IS_Bitrix24.Config.CalendarRegTask;
			whereIDtask = typeof whereIDtask !== 'undefined' ? whereIDtask : IS_Bitrix24.Config.CalendarRegTaskArrayResult;
			let userID = IS_Bitrix24.User.getIDFromUrl();
			if (!userID) {
				return;
			};
			let items = document.querySelectorAll('.calendar-simple-view-popup.--open');
			items.forEach((item) => {
				let allreadyLink = item.querySelector('.popup-window-buttons a.task_link');
				if (!!allreadyLink) {
					return;
				}
				if (typeof item == 'undefined' || !item) {
					return;
				}
				let title = item.querySelector('input.calendar-field[placeholder="Название события"]')
				if (typeof title == 'undefined' || !title) {
					return;
				}
				let titleParse = title.getAttribute('title')?.match(regTask);
				if (!titleParse) {
					titleParse = title.value?.match(regTask);
				}
				if (!titleParse) {
					return;
				}
				if (typeof titleParse[whereIDtask] == 'undefined' || typeof titleParse[whereIDtask] == 'NaN' || !titleParse[whereIDtask]) {
					return;
				}
				let link = IS_Bitrix24.Task.createLink(userID, titleParse[whereIDtask]);
				link.setAttribute('class', "ui-btn ui-btn-light-border task_link ");
				let buttons = item.querySelector('.popup-window-buttons')
				buttons.insertBefore(link, buttons.firstChild);
			});
		},
		createLink(userID) {
			let link = document.createElement('a');
			link.href = '/company/personal/user/' + userID + '/calendar/';
			link.target = '_blank';
			link.textContent = 'Календарь';
			link.addEventListener('click', (event) => {
				if (IS_Bitrix24.Config.OpenLinksNewWindow) {
					window.open(link.href, '_blank');
				}
				event.stopPropagation();
			});

			return link;
		}

	},
	Task: {
		createLink(userID, taskID) {
			let link = document.createElement('a');
			link.href = '/company/personal/user/' + userID + '/tasks/task/view/' + taskID + '/';
			link.target = '_blank';
			link.textContent = 'Задача';
			link.addEventListener('click', (event) => {
				if (IS_Bitrix24.Config.OpenLinksNewWindow) {
					window.open(link.href, '_blank');
				}
				event.stopPropagation();
			});
			return link;
		},
		getTaskIDFromUrl() {
			let task = window.location.href.match(/task\/view\/(\d+)\//);
			if (typeof task[1] == 'undefined' || !task[1]) {
				return -1;
			}
			return task[1];
		},
		AddLinkTaskCalendar() {
			let taskID = IS_Bitrix24.Task.getTaskIDFromUrl();
			if (!taskID) {
				return;
			}
			let taskUsers = document.querySelectorAll('.task-detail-sidebar-info-user-wrap');
			if (!taskUsers || taskUsers.length == 0) {
				document.querySelectorAll('iframe').forEach(item =>
					taskUsers = item.contentWindow.document.body.querySelectorAll('.task-detail-sidebar-info-user-wrap')
				)
			}
			if (!taskUsers || taskUsers.length == 0) {
				return;
			}
			taskUsers.forEach((taskUser) => {
				let userID = taskUser.getAttribute('data-item-value');
				if (!userID) {
					return;
				};
				let allreadyLink = taskUser.querySelector('a.calendar_link');
				if (!!allreadyLink) {
					return;
				}
				let link = IS_Bitrix24.Calendar.createLink(userID);
				link.href = link.href + '?hltaskid=' + taskID;
				link.setAttribute('class', 'calendar_link');
				taskUser.appendChild(link);
			})
		}
	}
}

IS_Bitrix24.AutoRun();
