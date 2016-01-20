#include <QVariant>
#include <QDebug>
#include "CalendarViewMonth.h"

CalendarViewMonth::CalendarViewMonth()
{
    _MainLayout = new QGridLayout();

    _MainLayout->setContentsMargins(0, 0, 0, 0);
    _MainLayout->setSpacing(0);

    QDate dateTmp(2016, 01, 04);

    for (int i = 0; i < 7; ++i)
    {
        QLabel *day = new QLabel(dateTmp.toString("dddd"));
        day->setFixedHeight(30);
        day->setAlignment(Qt::AlignCenter);
        _MainLayout->addWidget(day, 0, i, 1, 1);
        dateTmp = dateTmp.addDays(1);
    }

    setLayout(_MainLayout);
    _AssociatedDate = QDate(2016, 03, 01);

    for (int i = 0; i < 200; ++i)
    {
        Event *event = new Event();
        event->Color = QColor(qrand() % 255, qrand() % 255, qrand() % 255);
        event->Title = "Event";
        int hour = qrand() % 21;
        int minute = qrand() % 60;
        event->Start.setDate(QDate(2016, 03, qrand() % 30));
        event->Start.setTime(QTime(hour, minute));
        event->End.setDate(event->Start.date());
        event->End.setTime(QTime(hour + 2, minute));
        event->EventId = i;
        _Events.push_back(event);
    }

    LoadWidgets();
}

void CalendarViewMonth::LoadEvents(QList<Event *> events, QDate date)
{
    CalendarView::LoadEvents(events, date);
    if (_Days.size() == 0)
        LoadWidgets();
    else
        ReloadWidgets();
}

void CalendarViewMonth::LoadWidgets()
{
    QDate creationDate = _AssociatedDate;
    if (creationDate.dayOfWeek() != 1)
        creationDate = creationDate.addDays(-(creationDate.dayOfWeek() - 1));
    for (int i = 0; i < 35; ++i)
    {
        CalendarEventMonth *newLabel = new CalendarEventMonth(creationDate);

        if (creationDate.month() != _AssociatedDate.month())
            newLabel->setDisabled(true);
        _MainLayout->addWidget(newLabel, i / 7 + 1, i % 7, 1, 1);
        _Days.push_back(newLabel);
        newLabel->LoadEvents(_Events, creationDate);
        creationDate = creationDate.addDays(1);
    }
}

void CalendarViewMonth::ReloadWidgets()
{
    QDate creationDate = _AssociatedDate;
    if (creationDate.dayOfWeek() != 1)
        creationDate = creationDate.addDays(-(creationDate.dayOfWeek() - 1));
    for (int i = 0; i < 35; ++i)
    {
        CalendarEventMonth *newLabel = _Days.at(i);
        if (creationDate.month() != _AssociatedDate.month())
            newLabel->setDisabled(true);
        _Days.push_back(newLabel);
        newLabel->LoadEvents(_Events, creationDate);
        creationDate = creationDate.addDays(1);
    }
}
