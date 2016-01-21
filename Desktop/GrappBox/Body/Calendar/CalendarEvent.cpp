#include <QDebug>
#include "CalendarEvent.h"

CalendarEvent::CalendarEvent(Event event, QWidget *parent) : QWidget(parent)
{
    _Label = new QLabel(QString("[") + event.Start.time().toString("hh:mm") + "] " + event.Title);

    setStyleSheet("background: " + event.Color.name() + ";");

    _MainLayout = new QHBoxLayout();
    _MainLayout->addWidget(_Label);
    _MainLayout->setSpacing(0);
    _MainLayout->setContentsMargins(2, 0, 0, 2);

    _Event = event;

    setLayout(_MainLayout);
}

const Event &CalendarEvent::GetEvent() const
{
    return _Event;
}
