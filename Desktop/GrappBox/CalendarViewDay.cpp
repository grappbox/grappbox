#include <QDebug>
#include "CalendarViewDay.h"

CalendarViewDay::CalendarViewDay()
{
    setFixedHeight(1920);

    _MainLayout = new QGridLayout();

    _MainLayout->setSpacing(0);
    _MainLayout->setContentsMargins(50, 0, 0, 5);
    setLayout(_MainLayout);
}

void CalendarViewDay::paintEvent(QPaintEvent *event)
{
    QPainter paint(this);
    QRect rect = this->geometry();
    QTime time(0, 0);
    for (int i = 1; i < 24; ++i)
    {
        paint.drawText(QRectF(0, rect.height() * (i - 1) / 24, 30, rect.height() / 25), time.toString("hh:mm"));
        paint.drawLine(QPointF(0, rect.height()* i / 24), QPointF(rect.width(), rect.height() * i / 24));
        time.setHMS(i, 0, 0);
    }
    paint.drawText(QRectF(0, rect.height() * (23) / 24, 30, rect.height() / 25), time.toString("hh:mm"));

    QTime timeC = QTime::currentTime();

    paint.setPen(QColor(255, 0, 0));
    float currentTime = (float)timeC.minute() / 60 + (float)timeC.hour();
    paint.drawLine(QPointF(0, (float)rect.height() / 24.0f * currentTime), QPointF(rect.width(), (float)rect.height() / 24.0f * currentTime));

    CalendarView::paintEvent(event);
}

int CalendarViewDay::GetMaximumColumnForEvent(Event *event, QMap<int, int> eventsOverlap) const
{
    int currentColumn = eventsOverlap[event->EventId];
    for (Event *env : _Events)
    {
        if (env->EventId != event->EventId && currentColumn < eventsOverlap[env->EventId] && event->IsOverlapping(*env))
        {
            int tmpCurrentColumn = GetMaximumColumnForEvent(env, eventsOverlap);
            if (tmpCurrentColumn > currentColumn)
                currentColumn = tmpCurrentColumn;
        }
    }
    return currentColumn;
}

void CalendarViewDay::LoadEvent()
{
    qDebug() << "Load event !";
    QMap<int, int> eventsOverlap;
    QList<int> differOverlap;

    qDebug() << "Check overlap !";
    for (Event *event : _Events)
    {
        eventsOverlap[event->EventId] = 1;
    }

    bool noOverlap = false;
    while (!noOverlap)
    {
        noOverlap = true;
        for (Event *event : _Events)
        {
            for (Event *eventCompar : _Events)
            {
                if (event->EventId != eventCompar->EventId
                        && eventsOverlap[event->EventId] == eventsOverlap[eventCompar->EventId]
                        && event->IsOverlapping(*eventCompar))
                {
                    eventsOverlap[event->EventId] = eventsOverlap[event->EventId] + 1;
                    noOverlap = false;
                    break;
                }
            }
            if (!noOverlap)
                break;
        }
    }

    int numberOfColumn = 1;
    for (int item : eventsOverlap)
    {
        if (!differOverlap.contains(item))
            differOverlap.push_back(item);
    }
    for (int item : differOverlap)
    {
        numberOfColumn *= item;
    }
    QWidget *baseWidgetSize = new QWidget();
    baseWidgetSize->setMaximumWidth(1);
    _MainLayout->addWidget(baseWidgetSize, 0, 0, 24 * 60, 1);

    for (Event *event : _Events)
    {
        int startMinute = event->Start.time().hour() * 60 + event->Start.time().minute();
        int endMinute = event->End.time().hour() * 60 + event->End.time().minute();

        CalendarEvent *cEvent = new CalendarEvent(*event);
        _EventsWidget.push_back(cEvent);
        int pair = eventsOverlap[event->EventId];
        int eventColumn = GetMaximumColumnForEvent(event, eventsOverlap);
        _MainLayout->addWidget(cEvent, startMinute, numberOfColumn / eventColumn * (pair - 1) + 1, endMinute - startMinute, numberOfColumn / eventColumn);
    }
    qDebug() << "Finished event load !";
}
