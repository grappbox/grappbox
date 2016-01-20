#ifndef CALENDAREVENT_H
#define CALENDAREVENT_H

#include <QWidget>
#include <QLabel>
#include <QStyle>
#include <QHBoxLayout>
#include <QDateTime>

struct Event
{
    int EventId;
    int ProjectId;
    int CreatorId;

    QDateTime Start;
    QDateTime End;
    QString Title;
    QString Description;
    QString EventTypeName;
    QColor Color;
    QString Project;

    bool IsOverlapping(const Event &event) const
    {
        return (Start <= event.End && event.Start <= End);
    }

    bool operator<(const Event &eventb) const
    {
        return (this->Start < eventb.Start);
    }

};


class CalendarEvent : public QWidget
{
    Q_OBJECT
public:
    explicit CalendarEvent(Event event, QWidget *parent = 0);

signals:

public slots:

private:
    Event _Event;

    QHBoxLayout *_MainLayout;

    QLabel *_Label;
};

#endif // CALENDAREVENT_H
