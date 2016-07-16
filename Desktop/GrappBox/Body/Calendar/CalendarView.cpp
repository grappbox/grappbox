#include "CalendarView.h"

CalendarView::CalendarView()
{

}

void CalendarView::LoadEvents(QList<Event *> events, QDate date)
{
    _Events = events;
    _AssociatedDate = date;
}

void CalendarView::HideProject(int id)
{
}

void CalendarView::ShowProject(int id)
{
}

void CalendarView::EventEdit(Event *event)
{
	emit NeedEdit(event);
}

void CalendarView::EventDelete(Event *event)
{
	emit NeedDelete(event);
}