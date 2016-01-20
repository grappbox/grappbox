#ifndef CALENDARVIEWMONTH_H
#define CALENDARVIEWMONTH_H

#include "Calendar/CalendarView.h"
#include "Calendar/CalendarEventMonth.h"

#include <QLabel>

#include <QList>

class CalendarViewMonth : public CalendarView
{
public:
    CalendarViewMonth();
    virtual void LoadEvents(QList<Event*> events, QDate date);

signals:

public slots:

private:
    void LoadWidgets();
    void ReloadWidgets();

    QGridLayout *_MainLayout;

    QList<CalendarEventMonth*> _Days;
};

#endif // CALENDARVIEWMONTH_H
