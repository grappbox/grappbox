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
    void LoadEvents(QList<Event *> events, QDate date);
    virtual void HideProject(int id);
    virtual void ShowProject(int id);

protected:
    virtual void paintEvent(QPaintEvent *event);

signals:

public slots:

private:
    void LoadEventInterne();
    int GetMaximumColumnForEvent(Event *event, QMap<int, int> eventsOverlap) const;

    QGridLayout     *_MainLayout;

    QList<CalendarEvent*>   _EventsWidget;
};

#endif // CALENDARVIEWDAY_H
