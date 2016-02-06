#include "CalendarViewWeek.h"

CalendarViewWeek::CalendarViewWeek()
{
    _MainLayout = new QHBoxLayout();
    _MainLayout->setContentsMargins(0, 0, 0, 0);
    _MainLayout->setSpacing(0);

    for (int i = 0; i < 7; ++i)
    {
        CalendarViewDay *view = new CalendarViewDay((i == 0));
        _ViewsDay.push_back(view);
        _MainLayout->addWidget(view, 1);
		connect(view, SIGNAL(NeedEdit(Event*)), this, SLOT(EventEdit(Event*)));
		connect(view, SIGNAL(NeedDelete(Event*)), this, SLOT(EventDelete(Event*)));
	}

    setLayout(_MainLayout);
}

void CalendarViewWeek::LoadEvents(QList<Event *> events, QDate date)
{
    CalendarView::LoadEvents(events, date);
    while (_AssociatedDate.dayOfWeek() != 1)
        _AssociatedDate = _AssociatedDate.addDays(-1);
    int currentDay = 0;
    for (CalendarViewDay *dayView : _ViewsDay)
    {
        dayView->LoadEvents(events, _AssociatedDate.addDays(currentDay));
        dayView->setFixedWidth(geometry().width() / 7);
        currentDay++;
    }
}

void CalendarViewWeek::HideProject(int id)
{
    for (CalendarViewDay *dayView : _ViewsDay)
    {
        dayView->HideProject(id);
    }
}

void CalendarViewWeek::ShowProject(int id)
{
    for (CalendarViewDay *dayView : _ViewsDay)
    {
        dayView->ShowProject(id);
    }
}
