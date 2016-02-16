#include <QDebug>
#include "CalendarViewDay.h"

CalendarViewDay::CalendarViewDay(bool viewHour)
{
	setFixedHeight(1920);

	setStyleSheet("border: red solid 2px;");

	_MainLayout = new QGridLayout();
	_ViewHour = viewHour;

	_MainLayout->setSpacing(0);
	_MainLayout->setContentsMargins(50, 0, 0, 5);
	setLayout(_MainLayout);
}

void CalendarViewDay::paintEvent(QPaintEvent *event)
{
	QPainter paint(this);
	QRect rect = this->geometry();
	QTime time(0, 0);
	for (int i = 1; i < 24; ++i)
	{
		if (_ViewHour)
			paint.drawText(QRectF(0, rect.height() * (i - 1) / 24, 30, rect.height() / 25), time.toString("hh:mm"));
		paint.drawLine(QPointF(0, rect.height()* i / 24), QPointF(rect.width(), rect.height() * i / 24));
		time.setHMS(i, 0, 0);
	}
	if (_ViewHour)
		paint.drawText(QRectF(0, rect.height() * (23) / 24, 30, rect.height() / 25), time.toString("hh:mm"));

	QTime timeC = QTime::currentTime();

	paint.setPen(QColor(255, 0, 0));
	float currentTime = (float)timeC.minute() / 60 + (float)timeC.hour();
	if (QDate::currentDate() == _AssociatedDate)
		paint.drawLine(QPointF(0, (float)rect.height() / 24.0f * currentTime), QPointF(rect.width(), (float)rect.height() / 24.0f * currentTime));

	CalendarView::paintEvent(event);
}

void CalendarViewDay::LoadEvents(QList<Event *> events, QDate date)
{
	_AssociatedDate = date;
	QList<Event*> _DayEvent;
	for (Event *event : events)
	{
		if (event->Start.date() <= date && event->End.date() >= date)
		{
			if (event->Start.date() != date || event->End.date() != date)
			{
				Event *newEvent = new Event(*event);
				if (event->Start.date() < date)
				{
					newEvent->Start.setDate(date);
					newEvent->Start.setTime(QTime(0, 0, 0));
				}
				if (event->End.date() > date)
				{
					newEvent->End.setDate(date);
					newEvent->End.setTime(QTime(24, 0, 0));
				}
				_DayEvent.push_back(newEvent);
			}
			else
				_DayEvent.push_back(event);
		}
	}
	_Events = _DayEvent;
	LoadEventInterne();
}

void CalendarViewDay::HideProject(int id)
{
	for (CalendarEvent *event : _EventsWidget)
	{
		if (event->GetEvent()->ProjectId == id)
			event->hide();
	}
}

void CalendarViewDay::ShowProject(int id)
{
	for (CalendarEvent *event : _EventsWidget)
	{
		if (event->GetEvent()->ProjectId == id)
			event->show();
	}
}

int CalendarViewDay::GetMaximumColumnForEvent(Event *event, QMap<int, int> eventsOverlap) const
{
	int currentColumn = eventsOverlap[event->EventId];
	for (Event *env : _Events)
	{
		if (env->EventId != event->EventId && currentColumn < eventsOverlap[env->EventId] && event->IsOverlapping(*env))
		{
			int tmpCurrentColumn = GetMaximumColumnForEvent(env, eventsOverlap);
			if (tmpCurrentColumn > currentColumn)
				currentColumn = tmpCurrentColumn;
		}
	}
	return currentColumn;
}

void CalendarViewDay::LoadEventInterne()
{
	while (QLayoutItem *item = _MainLayout->takeAt(0))
	{
		if (item->widget())
			delete item->widget();
		delete item;
	}

	QMap<int, int> eventsOverlap;
	QList<int> differOverlap;

	for (Event *event : _Events)
	{
		eventsOverlap[event->EventId] = 1;
	}

	bool noOverlap = false;
	while (!noOverlap)
	{
		noOverlap = true;
		for (Event *event : _Events)
		{
			for (Event *eventCompar : _Events)
			{
				if (event->EventId != eventCompar->EventId
					&& eventsOverlap[event->EventId] == eventsOverlap[eventCompar->EventId]
					&& event->IsOverlapping(*eventCompar))
				{
					eventsOverlap[event->EventId] = eventsOverlap[event->EventId] + 1;
					noOverlap = false;
					break;
				}
			}
			if (!noOverlap)
				break;
		}
	}

	int numberOfColumn = 1;
	for (int item : eventsOverlap)
	{
		if (!differOverlap.contains(item))
			differOverlap.push_back(item);
	}
	for (int item : differOverlap)
	{
		numberOfColumn *= item;
	}
	QWidget *baseWidgetSize = new QWidget();
	baseWidgetSize->setMaximumWidth(1);
	_MainLayout->addWidget(baseWidgetSize, 0, 0, 24 * 60, 1);

	for (Event *event : _Events)
	{
		int startMinute = event->Start.time().hour() * 60 + event->Start.time().minute();
		int endMinute = event->End.time().hour() * 60 + event->End.time().minute();

		CalendarEvent *cEvent = new CalendarEvent(event);
		connect(cEvent, SIGNAL(NeedEdit(Event*)), this, SLOT(EventEdit(Event*)));
		connect(cEvent, SIGNAL(NeedDelete(Event*)), this, SLOT(EventDelete(Event*)));
		_EventsWidget.push_back(cEvent);
		int pair = eventsOverlap[event->EventId];
		int eventColumn = GetMaximumColumnForEvent(event, eventsOverlap);
		_MainLayout->addWidget(cEvent, startMinute, numberOfColumn / eventColumn * (pair - 1) + ((_ViewHour) ? 1 : 0), endMinute - startMinute, numberOfColumn / eventColumn);
	}
}
