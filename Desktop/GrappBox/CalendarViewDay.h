#ifndef CALENDARVIEWDAY_H
#define CALENDARVIEWDAY_H

#include "Calendar/CalendarView.h"
#include "Calendar/CalendarEvent.h"

#include <QGridLayout>

#include <QList>
#include <QPair>
#include <QMap>

#include <QPaintEvent>
#include <QPainter>
#include <QRect>

class CalendarViewDay : public CalendarView
{
public:
    CalendarViewDay();

protected:
    virtual void paintEvent(QPaintEvent *event);

signals:

public slots:

private:
    void LoadEvent();
    int GetMaximumColumnForEvent(Event *event, QMap<int, int> eventsOverlap) const;

    QGridLayout     *_MainLayout;

    QList<CalendarEvent*>   _EventsWidget;
};

#endif // CALENDARVIEWDAY_H
