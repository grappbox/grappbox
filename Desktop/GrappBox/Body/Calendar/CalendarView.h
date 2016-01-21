#ifndef CALENDARVIEW_H
#define CALENDARVIEW_H

#include <QWidget>
#include <QDateTime>
#include <QColor>

#include <QList>

#include "Calendar/CalendarEvent.h"

class CalendarView : public QWidget
{
public:
    CalendarView();
    virtual void LoadEvents(QList<Event*> events, QDate date);
    virtual void HideProject(int id);
    virtual void ShowProject(int id);

signals:

public slots:

protected:
    QList<Event*> _Events;
    QDate _AssociatedDate;
};

#endif // CALENDARVIEW_H
