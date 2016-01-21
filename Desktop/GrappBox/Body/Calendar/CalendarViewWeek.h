#ifndef CALENDARVIEWWEEK_H
#define CALENDARVIEWWEEK_H

#include <QList>

#include "CalendarViewDay.h"

class CalendarViewWeek : public CalendarView
{
public:
    CalendarViewWeek();
    virtual void LoadEvents(QList<Event *> events, QDate date);
    virtual void HideProject(int id);
    virtual void ShowProject(int id);

signals:

public slots:

private:
    QHBoxLayout *_MainLayout;

    QList<CalendarViewDay*>   _ViewsDay;


};

#endif // CALENDARVIEWWEEK_H
