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

    Event()
    {
        EventId = 0;
        ProjectId = 0;
        CreatorId = 0;
        Start = QDateTime();
        End = QDateTime();
        Title = "";
        Description = "";
        EventTypeName = "";
        Color = QColor();
        Project = "";
    }

    Event(const Event& copy)
    {
        EventId = copy.EventId;
        ProjectId = copy.ProjectId;
        CreatorId = copy.CreatorId;
        Start = copy.Start;
        End = copy.End;
        Title = copy.Title;
        Description = copy.Description;
        EventTypeName = copy.EventTypeName;
        Color = copy.Color;
        Project = copy.Project;
    }

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

    const Event &GetEvent() const;

signals:

public slots:

private:
    Event _Event;

    QHBoxLayout *_MainLayout;

    QLabel *_Label;
};

#endif // CALENDAREVENT_H
