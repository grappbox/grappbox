#ifndef CALENDARVIEW_H
#define CALENDARVIEW_H

#include <QWidget>
#include <QDateTime>
#include <QColor>

#include <QList>

#include "Calendar/CalendarEvent.h"

class CalendarView : public QWidget
{
	Q_OBJECT
public:
    CalendarView();
    virtual void LoadEvents(QList<Event*> events, QDate date);
    virtual void HideProject(int id);
    virtual void ShowProject(int id);

signals:
	void NeedEdit(Event *);
	void NeedDelete(Event *);

public slots:
	void EventEdit(Event *event);
	void EventDelete(Event *event);

protected:
    QList<Event*> _Events;
    QDate _AssociatedDate;
};

#endif // CALENDARVIEW_H
