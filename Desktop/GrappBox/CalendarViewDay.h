#ifndef CALENDARVIEWDAY_H
#define CALENDARVIEWDAY_H

#include "Calendar/CalendarView.h"
#include "Calendar/CalendarEvent.h"
#include "Calendar/CalendarViewDayContainer.h"

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
    CalendarViewDay(bool viewHour = true);
    void LoadEvents(QList<Event *> events, QDate date);
    virtual void HideProject(int id);
    virtual void ShowProject(int id);

signals:

public slots:

    QLabel          *_DayName;
    CalendarViewDayContainer *_Container;
};

#endif // CALENDARVIEWDAY_H
