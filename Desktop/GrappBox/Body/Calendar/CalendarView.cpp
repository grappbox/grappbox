#include "CalendarView.h"

CalendarView::CalendarView()
{

}

void CalendarView::LoadEvents(QList<Event *> events, QDate date)
{
    _Events = events;
    _AssociatedDate = date;
}
