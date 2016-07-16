#ifndef CALENDARVIEWDAYCONTAINER_H
#define CALENDARVIEWDAYCONTAINER_H

#include "Calendar/CalendarView.h"
#include "Calendar/CalendarEvent.h"

#include <QGridLayout>

#include <QList>
#include <QPair>
#include <QMap>

#include <QPaintEvent>
#include <QPainter>
#include <QRect>
#include <QWidget>

class CalendarViewDayContainer : public CalendarView
{
    Q_OBJECT
public:
    explicit CalendarViewDayContainer(bool viewHour);
    void LoadEvents(QList<Event *> events, QDate date);
    virtual void HideProject(int id);
    virtual void ShowProject(int id);

protected:
    virtual void paintEvent(QPaintEvent *event);

private:
    void LoadEventInterne();
    int GetMaximumColumnForEvent(Event *event, QMap<int, int> eventsOverlap) const;

    QGridLayout     *_MainLayout;

    QList<CalendarEvent*>   _EventsWidget;

    bool _ViewHour;
};

#endif // CALENDARVIEWDAYCONTAINER_H
