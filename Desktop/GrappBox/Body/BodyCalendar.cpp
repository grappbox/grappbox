#include <QDebug>
#include <QScrollArea>
#include "SDataManager.h"
#include "utils.h"
#include "Calendar/CalendarEventForm.h"
#include "BodyCalendar.h"

BodyCalendar::BodyCalendar()
{
	_CurrentDrawingDate = QDate::currentDate();
	_LastDrawingDate = QDate::currentDate();

	_View = MONTH;

	_MainLayout = new QHBoxLayout();
	_MainLayout->setContentsMargins(0, 0, 0, 0);
	_MainLayout->setSpacing(0);

	_CalendarLayout = new QVBoxLayout();
	_CalendarLayout->setContentsMargins(0, 0, 0, 0);
	_CalendarLayout->setSpacing(0);

	_SideBarLayout = new QVBoxLayout();
	_SideBarLayout->setContentsMargins(10, 0, 0, 0);
	_SideBarLayout->setSpacing(0);

	_ViewCalendarLayout = new QStackedLayout();
	_ViewCalendarLayout->setContentsMargins(0, 0, 0, 0);
	_ViewCalendarLayout->setSpacing(0);

	_ProjectChoiceLayout = new QVBoxLayout();
	_ProjectChoiceLayout->setContentsMargins(0, 0, 0, 0);
	_ProjectChoiceLayout->setSpacing(0);

	_TaskChoiceLayout = new QVBoxLayout();
	_TaskChoiceLayout->setContentsMargins(0, 0, 0, 0);
	_TaskChoiceLayout->setSpacing(0);

	_TopBarLayout = new QHBoxLayout();
	_TopBarLayout->setContentsMargins(0, 0, 0, 0);
	_TopBarLayout->setSpacing(0);

	_NewEvent = new QPushButton("Create");
	_NewEvent->setMaximumHeight(80);

	_LabelMonthCalendar = new QLabel(_CurrentDrawingDate.toString("MMMM, yyyy"));
	_LabelMonthCalendar->setMaximumHeight(20);
	_MonthCalendarFixed = new QCalendarWidget();
	_MonthCalendarFixed->setCurrentPage(_CurrentDrawingDate.year(), _CurrentDrawingDate.month());
	_MonthCalendarFixed->setDateEditEnabled(false);
	_MonthCalendarFixed->setNavigationBarVisible(false);
	_MonthCalendarFixed->setVerticalHeaderFormat(QCalendarWidget::NoVerticalHeader);
	_MonthCalendarFixed->setSelectionMode(QCalendarWidget::NoSelection);
	_MonthCalendarFixed->setMaximumHeight(200);
	_MonthCalendarFixed->setFirstDayOfWeek(Qt::Monday);

	_ProjectChoice = new QLabel("Projects");
	_TaskChoice = new QLabel("Tasks");

	_PreviousDate = new QPushButton("<");
	_PreviousDate->setMaximumWidth(60);
	_NextDate = new QPushButton(">");
	_NextDate->setMaximumWidth(60);
	_CurrentDate = new QLabel(_CurrentDrawingDate.toString("MMMM, yyyy"));
	_CurrentDate->setAlignment(Qt::AlignCenter);
	_CurrentDate->setMinimumHeight(20);
	_ToDay = new QPushButton("Day");
	_ToDay->setCheckable(true);
	_ToDay->setMaximumWidth(90);
	_ToWeek = new QPushButton("Week");
	_ToWeek->setMaximumWidth(90);
	_ToWeek->setCheckable(true);
	_ToMonth = new QPushButton("Month");
	_ToMonth->setMaximumWidth(90);
	_ToMonth->setCheckable(true);
	_ToMonth->setChecked(true);
	_ToMonth->setDisabled(true);
	_ToToday = new QPushButton("Today");
	_ToToday->setMaximumWidth(90);

	_ViewMonth = new CalendarViewMonth();
	_ViewWeek = new CalendarViewWeek();
	_ViewDay = new CalendarViewDay();

	connect(_ViewMonth, SIGNAL(NeedEdit(Event*)), this, SLOT(OnEditEvent(Event*)));
	connect(_ViewMonth, SIGNAL(NeedDelete(Event*)), this, SLOT(OnDeleteEvent(Event*)));
	connect(_ViewWeek, SIGNAL(NeedEdit(Event*)), this, SLOT(OnEditEvent(Event*)));
	connect(_ViewWeek, SIGNAL(NeedDelete(Event*)), this, SLOT(OnDeleteEvent(Event*)));
	connect(_ViewDay, SIGNAL(NeedEdit(Event*)), this, SLOT(OnEditEvent(Event*)));
	connect(_ViewDay, SIGNAL(NeedDelete(Event*)), this, SLOT(OnDeleteEvent(Event*)));

	QScrollArea *_WeekScrollArea = new QScrollArea();
	_WeekScrollArea->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);
	_WeekScrollArea->setWidgetResizable(true);
	_WeekScrollArea->setWidget(_ViewWeek);
	QScrollArea *_DayScrollArea = new QScrollArea();
	_DayScrollArea->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
	_DayScrollArea->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);
	_DayScrollArea->setWidgetResizable(true);
	_DayScrollArea->setWidget(_ViewDay);

	QWidget *wSideBarLayout = new QWidget();
	wSideBarLayout->setLayout(_SideBarLayout);
	wSideBarLayout->setFixedWidth(300);

	_MainLayout->addLayout(_CalendarLayout);
	_MainLayout->addWidget(wSideBarLayout);

	_SideBarLayout->addWidget(_NewEvent);
	_SideBarLayout->addWidget(_LabelMonthCalendar);
	_SideBarLayout->addWidget(_MonthCalendarFixed);
	_SideBarLayout->addWidget(_ProjectChoice);
	_SideBarLayout->addLayout(_ProjectChoiceLayout);
	_SideBarLayout->addWidget(_TaskChoice);
	_SideBarLayout->addLayout(_TaskChoiceLayout);
	_SideBarLayout->addSpacing(1080);

	QWidget *wTopBarLayout = new QWidget();
	wTopBarLayout->setLayout(_TopBarLayout);
	wTopBarLayout->setMaximumHeight(60);

	_CalendarLayout->addWidget(wTopBarLayout);
	_CalendarLayout->addLayout(_ViewCalendarLayout);

	_ViewCalendarLayout->addWidget(_ViewMonth);
	_ViewCalendarLayout->addWidget(_WeekScrollArea);
	_ViewCalendarLayout->addWidget(_DayScrollArea);

	_TopBarLayout->addWidget(_PreviousDate);
	_TopBarLayout->addWidget(_CurrentDate);
	_TopBarLayout->addWidget(_NextDate);
	_TopBarLayout->addWidget(_ToDay);
	_TopBarLayout->addWidget(_ToWeek);
	_TopBarLayout->addWidget(_ToMonth);
	_TopBarLayout->addWidget(_ToToday);

	setLayout(_MainLayout);

	UpdateType();

	QObject::connect(_ToDay, SIGNAL(clicked(bool)), this, SLOT(OnDayCheckedChange(bool)));
	QObject::connect(_ToWeek, SIGNAL(clicked(bool)), this, SLOT(OnWeekCheckedChange(bool)));
	QObject::connect(_ToMonth, SIGNAL(clicked(bool)), this, SLOT(OnMonthCheckedChange(bool)));

	QObject::connect(_ToToday, SIGNAL(clicked(bool)), this, SLOT(OnMoveToday()));

	QObject::connect(_NextDate, SIGNAL(clicked(bool)), this, SLOT(OnNext()));
	QObject::connect(_PreviousDate, SIGNAL(clicked(bool)), this, SLOT(OnPrev()));

	QObject::connect(_NewEvent, SIGNAL(clicked(bool)), this, SLOT(OnCreate()));
}

void BodyCalendar::OnWeekCheckedChange(bool value)
{
	qDebug() << value;
	_ToMonth->setDisabled(false);
	_ToDay->setDisabled(false);
	_ToWeek->setDisabled(true);
	_ToMonth->setChecked(false);
	_ToDay->setChecked(false);
	if (_View != WEEK)
	{
		_View = WEEK;
		UpdateType();
	}
}

void BodyCalendar::OnMonthCheckedChange(bool value)
{
	qDebug() << value;
	_ToMonth->setDisabled(true);
	_ToDay->setDisabled(false);
	_ToWeek->setDisabled(false);
	_ToDay->setChecked(false);
	_ToWeek->setChecked(false);
	if (_View != MONTH)
	{
		_View = MONTH;
		UpdateType();
	}
}

void BodyCalendar::OnEventLoadingDone(int id, QByteArray data)
{
	QJsonDocument doc = QJsonDocument::fromJson(data);
	QJsonArray arrayEvent = doc.object()["data"].toObject()["array"].toObject()["events"].toArray();
	QJsonArray taskEvent = doc.object()["data"].toObject()["array"].toObject()["task"].toArray();
	QString format = "yyyy-MM-dd HH:mm:ss.zzzz";
	QList<int> projectToLoad;
	for (QJsonValueRef ref : arrayEvent)
	{
		QJsonObject obj = ref.toObject();
		Event *eve = new Event();
		QString dateStart = obj["beginDate"].toObject()["date"].toString();
		QString dateEnd = obj["endDate"].toObject()["date"].toString();
		QDateTime startDate = QDateTime::fromString(dateStart, format);
		QDateTime endDate = QDateTime::fromString(dateEnd, format);
		eve->Start = startDate;
		eve->End = endDate;
		eve->CreatorId = obj["creator"].toObject()["id"].toInt();
		eve->ProjectId = obj["projectId"].toInt();
		eve->Icon = obj["icon"].toString();
		if (!projectToLoad.contains(eve->ProjectId))
		{
			projectToLoad.push_back(eve->ProjectId);
		}
		eve->EventId = obj["id"].toInt();
		eve->EventTypeName = obj["type"].toObject()["name"].toString();
		eve->EventTypeId = obj["type"].toObject()["id"].toInt();
		eve->Title = obj["title"].toString();
		eve->Color = QColor(128, 50, 235);
		eve->Description = obj["description"].toString();
		QDate monthStart(startDate.date().year(), startDate.date().month(), 1);
		QDate monthEnd(endDate.date().year(), endDate.date().month(), 1);
		while (monthStart <= monthEnd)
		{
			_MapMonthEvent[monthStart].push_back(eve);
			monthStart = monthStart.addMonths(1);
		}
	}
	for (QJsonValueRef ref : taskEvent)
	{
		Task *task = new Task();
		QJsonObject obj = ref.toObject();
		task->TaskId = obj["id"].toInt();
		task->ProjectId = obj["projectId"].toInt();
		task->Title = obj["title"].toString();
		QString dateStart = obj["startedAt"].toObject()["date"].toString();
		task->Start = QDateTime::fromString(dateStart, format);
		QString dateEnd = obj["dueDate"].toObject()["date"].toString();
		task->End = QDateTime::fromString(dateEnd, format);
		_MapMonthTask.push_back(task);
		if (!projectToLoad.contains(task->ProjectId))
		{
			projectToLoad.push_back(task->ProjectId);
		}
	}
	_LoadingDates.remove(id);
	qDebug() << "Dates Loading !";
	qDebug() << "Project : " << _LoadingProjects.size();
	qDebug() << "Dates : " << _LoadingDates.size();
	qDebug() << "Projects loaded : " << _IsProjectsLoaded;
	if (_LoadingDates.size() == 0)
	{
		for (int id : projectToLoad)
		{
			QVector<QString> data;
			data.push_back(USER_TOKEN);
			data.push_back(TO_STRING(id));
			int requestId = API::SDataManager::GetCurrentDataConnector()->Get(API::DP_PROJECT, API::GR_PROJECT, data, this, "OnProjectLoadingDone", "OnProjectLoadingFail");
			_LoadingProjects[requestId] = id;
		}
		qDebug() << "Loading projects : " << projectToLoad.size();
	}
}

void BodyCalendar::OnEventLoadingFail(int, QByteArray data)
{
}

void BodyCalendar::OnProjectLoadingDone(int requestId, QByteArray data)
{
	QJsonDocument doc = QJsonDocument::fromJson(data);
	QJsonObject obj = doc.object()["data"].toObject();

	int id = _LoadingProjects[requestId];
	QString name = obj["name"].toString();
	QString color = obj["color"].toString();
	color = "#" + color.toUpper();

	bool exist = _ProjectChoiceCheckBox.contains(id);

	_Projects[id] = name;
	_ProjectsColors[id] = color;
	if (exist)
	{
		_ProjectChoiceLayout->removeWidget(_ProjectChoiceCheckBox[id]);
		delete _ProjectChoiceCheckBox[id];
	}
	QCheckBox *checkbox = new QCheckBox(name);
	_ProjectChoiceLayout->addWidget(checkbox);
	checkbox->setChecked(true);
	connect(checkbox, SIGNAL(clicked(bool)), this, SLOT(OnProjectCheckChange()));
	_ProjectChoiceCheckBox[id] = checkbox;
	checkbox->setStyleSheet("color: " + color + ";");

	for (QList<Event*> list : _MapMonthEvent)
	{
		for (Event *env : list)
		{
			if (env->ProjectId == id)
			{
				env->Project = name;
				env->Color = QColor(color);
			}
		}
	}

	_LoadingProjects.remove(requestId);
	qDebug() << "Project Loading !";
	qDebug() << "Project : " << _LoadingProjects.size();
	qDebug() << "Dates : " << _LoadingDates.size();
	qDebug() << "Projects loaded : " << _IsProjectsLoaded;
	if (_LoadingProjects.size() == 0 && _LoadingDates.size() == 0 && _IsProjectsLoaded)
	{
		_IsLoaded = false;
		UpdateType();
	}
}

void BodyCalendar::OnProjectLoadingFail(int, QByteArray data)
{
}

void BodyCalendar::OnDayCheckedChange(bool value)
{
	_ToMonth->setDisabled(false);
	_ToDay->setDisabled(true);
	_ToWeek->setDisabled(false);
	_ToMonth->setChecked(false);
	_ToWeek->setChecked(false);
	if (_View != DAY)
	{
		_View = DAY;
		UpdateType();
	}
}

void BodyCalendar::OnNext()
{
	switch (_View)
	{
	case DAY:
		_CurrentDrawingDate = _CurrentDrawingDate.addDays(1);
		break;
	case MONTH:
		_CurrentDrawingDate = _CurrentDrawingDate.addMonths(1);
		break;
	case WEEK:
		_CurrentDrawingDate = _CurrentDrawingDate.addDays(7);
		break;
	}
	UpdateType();
}

void BodyCalendar::OnPrev()
{
	switch (_View)
	{
	case DAY:
		_CurrentDrawingDate = _CurrentDrawingDate.addDays(-1);
		break;
	case MONTH:
		_CurrentDrawingDate = _CurrentDrawingDate.addMonths(-1);
		break;
	case WEEK:
		_CurrentDrawingDate = _CurrentDrawingDate.addDays(-7);
		break;
	}
	UpdateType();
}

void BodyCalendar::OnProjectCheckChange()
{
	QCheckBox *sender = dynamic_cast<QCheckBox*>(QObject::sender());
	if (sender == nullptr)
		return;
	for (QMap<int, QCheckBox*>::iterator it = _ProjectChoiceCheckBox.begin(); it != _ProjectChoiceCheckBox.end(); ++it)
	{
		if (it.value() == sender)
		{
			if (sender->isChecked())
			{
				_ViewMonth->ShowProject(it.key());
				_ViewWeek->ShowProject(it.key());
				_ViewDay->ShowProject(it.key());
			}
			else
			{
				_ViewMonth->HideProject(it.key());
				_ViewWeek->HideProject(it.key());
				_ViewDay->HideProject(it.key());
			}
		}
	}
}

void BodyCalendar::OnCreate()
{
	CalendarEventForm *form = new CalendarEventForm(nullptr, _AllProjects, this);
	QObject::connect(form, SIGNAL(Create(QDateTime, QDateTime)), this, SLOT(OnCreateConfirm(QDateTime, QDateTime)));
	form->exec();
	qDebug() << "Form finish execution";
}

void BodyCalendar::OnCreateConfirm(QDateTime start, QDateTime end)
{
	QDate loadablefirst(_CurrentDrawingDate.year(), _CurrentDrawingDate.month(), 1);
	QDate loadablecurrent = loadablefirst.addMonths(1);
	QDate loadablelast = loadablecurrent.addMonths(1);

	QDate realStart(start.date().year(), start.date().month(), 1);
	QDate realEnd(end.date().year(), end.date().month(), 1);

	QVector<QString> data;
	data.push_back(USER_TOKEN);
	QDate date = loadablefirst;
	if (date >= realStart && date <= realEnd)
	{
		data.push_back(date.toString("yyyy-MM-dd"));
		_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = date;
	}
	date = loadablecurrent;
	if (date >= realStart && date <= realEnd)
	{
		data[1] = date.toString("yyyy-MM-dd");
		_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = date;
	}
	date = loadablelast;
	if (date >= realStart && date <= realEnd)
	{
		data[1] = date.toString("yyyy-MM-dd");
		_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = date;
	}
}

void BodyCalendar::OnMoveToday()
{
	_CurrentDrawingDate = QDate::currentDate();
	UpdateType();
}

void BodyCalendar::OnEditEvent(Event *event)
{
	CalendarEventForm *form = new CalendarEventForm(event, _AllProjects, this);
	form->exec();
	QVector<QString> data;
	data.push_back(USER_TOKEN);
	QDate date = event->Start.date();
	date.setDate(date.year(), date.month(), 01);
	data.push_back(date.toString("yyyy-MM-dd"));
	while (date.year() != event->End.date().year() && date.month() != event->End.date().month())
	{
		_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = date;
		date = date.addMonths(1);
		data[1] = date.toString("yyyy-MM-dd");
	}
}

void BodyCalendar::OnDeleteEvent(Event *event)
{
	qDebug() << "Delete event " + event->EventId;
	QVector<QString> data;
	data.push_back(USER_TOKEN);
	data.push_back(TO_STRING(event->EventId));
	int requestId = DATA_CONNECTOR->Delete(API::DP_CALENDAR, API::DR_REMOVE_EVENT, data, this, "OnDeleteDone", "OnDeleteFail");
	_DeleteEvent[requestId] = event->EventId;
}

void BodyCalendar::OnDeleteDone(int id, QByteArray data)
{
	for (QMap<QDate, QList<Event*> >::iterator it = _MapMonthEvent.begin(); it != _MapMonthEvent.end(); ++it)
	{
		QList<Event*> toDelete;
		QList<Event*> &currentList = it.value();
		for (Event *event : currentList)
		{
			if (event->EventId == _DeleteEvent[id])
				toDelete.push_back(event);
		}
		for (Event *event : toDelete)
		{
			currentList.removeAll(event);
		}
	}
	UpdateType();
}

void BodyCalendar::OnDeleteFail(int id, QByteArray data)
{

}

void BodyCalendar::OnLoadingProjectsDone(int id, QByteArray data)
{
	QJsonDocument doc = QJsonDocument::fromJson(data);
	QJsonArray projectList = doc.object()["data"].toObject()["array"].toArray();
	for (QJsonValueRef ref : projectList)
	{
		QJsonObject obj = ref.toObject();
		_AllProjects[obj["project_id"].toInt()] = obj["project_name"].toString();
	}
	_IsProjectsLoaded = true;
	if (_LoadingProjects.size() == 0 && _LoadingDates.size() == 0 && _IsProjectsLoaded)
	{
		_IsLoaded = false;
		UpdateType();
	}
}

void BodyCalendar::OnLoadingProjectsFail(int id, QByteArray data)
{
}

void BodyCalendar::UpdateType()
{
	qDebug() << "Update type !";
	QList<Event> presentEvent;
	for (QMap<QDate, QList<Event*> >::iterator it = _MapMonthEvent.begin(); it != _MapMonthEvent.end(); ++it)
	{
		QList<Event*> toRemove;
		for (Event *item : it.value())
		{
			if (presentEvent.contains(*item))
			{
				toRemove.push_back(item);
			}
			else
				presentEvent.push_back(*item);
		}
		for (Event *item : toRemove)
		{
			it.value().removeAll(item);
		}
	}
	QList<Task> presentTask;
	QList<Task*> toRemove;
	for (Task *item : _MapMonthTask)
	{
		if (presentTask.contains(*item))
		{
			toRemove.push_back(item);
		}
		else
			presentTask.push_back(*item);
	}
	for (Task *item : toRemove)
	{
		_MapMonthTask.removeAll(item);
	}
	if (_LastDrawingDate.month() != _CurrentDrawingDate.month())
	{
		QDate keyDate = QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month() + 1, 1);
		QVector<QString> data;
		data.push_back(USER_TOKEN);
		data.push_back(keyDate.toString("yyyy-MM-dd"));
		_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = keyDate;
		keyDate = QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month() - 1, 1);
		data.clear();
		data.push_back(USER_TOKEN);
		data.push_back(keyDate.toString("yyyy-MM-dd"));
		_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = keyDate;
		_LastDrawingDate = _CurrentDrawingDate;
		_ViewMonth->LoadEvents(_MapMonthEvent[keyDate], _CurrentDrawingDate);
		_MonthCalendarFixed->setCurrentPage(_CurrentDrawingDate.year(), _CurrentDrawingDate.month());
	}
	switch (_View)
	{
	case DAY:
		_CurrentDate->setText(_CurrentDrawingDate.toString("dddd, dd MMMM yyyy"));
		break;
	case MONTH:
		_CurrentDate->setText(_CurrentDrawingDate.toString("MMMM yyyy"));
		break;
	case WEEK:
		QDate mondayDate = _CurrentDrawingDate;
		while (mondayDate.dayOfWeek() != 1)
			mondayDate = mondayDate.addDays(-1);
		QDate sundayDate = mondayDate.addDays(6);
		QString firstDate = "";
		if (mondayDate.year() != sundayDate.year())
			firstDate = mondayDate.toString("dd MMMM yyyy");
		else if (mondayDate.month() != sundayDate.month())
			firstDate = mondayDate.toString("dd MMMM");
		else
			firstDate = mondayDate.toString("dd");
		_CurrentDate->setText(firstDate + " - " + sundayDate.toString("dd MMMM yyyy"));
		break;
	}
	_LabelMonthCalendar->setText(_CurrentDrawingDate.toString("MMMM, yyyy"));
	QList<Event*> currentEvents = _MapMonthEvent[QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month(), 1)];
	if (_CurrentDrawingDate.month() == 1)
		currentEvents.append(_MapMonthEvent[QDate(_CurrentDrawingDate.year() - 1, 12, 1)]);
	else
		currentEvents.append(_MapMonthEvent[QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month() - 1, 1)]);
	if (_CurrentDrawingDate.month() == 12)
		currentEvents.append(_MapMonthEvent[QDate(_CurrentDrawingDate.year() + 1, 1, 1)]);
	else
		currentEvents.append(_MapMonthEvent[QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month() + 1, 1)]);
	QDate start;
	QDate end;
	switch (_View)
	{
	case DAY:
		start = _CurrentDrawingDate;
		end = _CurrentDrawingDate;
		_ViewDay->LoadEvents(currentEvents, _CurrentDrawingDate);
		_ViewCalendarLayout->setCurrentIndex(2);
		break;
	case MONTH:
		start = QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month(), 1);
		end = start.addMonths(1).addDays(-1);
		_ViewMonth->LoadEvents(currentEvents, _CurrentDrawingDate);
		_ViewCalendarLayout->setCurrentIndex(0);
		break;
	case WEEK:
		start = _CurrentDrawingDate;
		while (start.dayOfWeek() != 1)
			start = start.addDays(-1);
		end = start.addDays(6);
		_ViewWeek->LoadEvents(currentEvents, _CurrentDrawingDate);
		_ViewCalendarLayout->setCurrentIndex(1);
		break;
	}
	while (QLayoutItem *item = _TaskChoiceLayout->takeAt(0))
	{
		if (item->widget())
			delete item->widget();
		delete item;
	}
	for (Task *task : _MapMonthTask)
	{
		if (task->Start.date() <= end && start <= task->End.date())
		{
			QLabel *lab = new QLabel(task->Title);
			lab->setStyleSheet("color: " + _ProjectsColors[task->ProjectId] + ";");
			_TaskChoiceLayout->addWidget(lab);
		}
	}
}

void BodyCalendar::Show(int ID, MainWindow *mainApp)
{
	_WidgetId = ID;
	_MainApp = mainApp;
	_IsLoaded = true;
	_IsProjectsLoaded = false;

	while (QLayoutItem *item = _ProjectChoiceLayout->takeAt(0))
	{
		if (item->widget())
			delete item->widget();
		delete item;
	}
	_ProjectChoiceCheckBox.clear();

	QVector<QString> data;
	data.push_back(USER_TOKEN);
	QDate date = QDate::currentDate();
	date.setDate(date.year(), date.month(), 01);
	data.push_back(date.toString("yyyy-MM-dd"));
	_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = date;
	date = date.addMonths(1);
	data[1] = date.toString("yyyy-MM-dd");
	_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = date;
	date = date.addMonths(-2);
	data[1] = date.toString("yyyy-MM-dd");
	_LoadingDates[API::SDataManager::GetCurrentDataConnector()->Get(API::DP_CALENDAR, API::GR_CALENDAR, data, this, "OnEventLoadingDone", "OnEventLoadingFail")] = date;
	data.clear();
	data.push_back(USER_TOKEN);
	DATA_CONNECTOR->Get(API::DP_PROJECT, API::GR_LIST_PROJECT, data, this, "OnLoadingProjectsDone", "OnLoadingProjectsFail");
	emit OnLoadingDone(ID);
}

void BodyCalendar::Hide()
{
}
