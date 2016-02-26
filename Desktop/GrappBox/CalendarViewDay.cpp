#include <QDebug>
#include "CalendarViewDay.h"

CalendarViewDay::CalendarViewDay(bool viewHour)
{
	setFixedHeight(1920);

	setStyleSheet("border: red solid 2px;");

    QVBoxLayout *mainLayout = new QVBoxLayout();
    _DayName = new QLabel("Monday");
    _DayName->setFixedHeight(30);
    _DayName->setAlignment(Qt::AlignCenter);
    _Container = new CalendarViewDayContainer(viewHour);
    connect(_Container, SIGNAL(NeedEdit(Event*)), this, SLOT(EventEdit(Event*)));
    connect(_Container, SIGNAL(NeedDelete(Event*)), this, SLOT(EventDelete(Event*)));

    mainLayout->addWidget(_DayName);
    mainLayout->addWidget(_Container);

    mainLayout->setSpacing(0);
    mainLayout->setContentsMargins(0, 0, 0, 0);
    setLayout(mainLayout);
}

void CalendarViewDay::LoadEvents(QList<Event *> events, QDate date)
{
    _AssociatedDate = date;
    _Events = events;
    _DayName->setText(QDate::longDayName(date.dayOfWeek()));
    _Container->LoadEvents(events, date);
}

void CalendarViewDay::HideProject(int id)
{
    _Container->HideProject(id);
}

void CalendarViewDay::ShowProject(int id)
{
    _Container->ShowProject(id);
}
