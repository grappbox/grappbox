#ifndef CALENDAREVENT_H
#define CALENDAREVENT_H

#include <QWidget>
#include <QLabel>
#include <QStyle>
#include <QHBoxLayout>
#include <QPushButton>
#include <QDateTime>
#include "PushButtonImage.h"

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
    explicit CalendarEvent(Event *event, QWidget *parent = 0);

	const Event *GetEvent() const;

protected:
	void enterEvent(QEvent *event);
	void leaveEvent(QEvent *event);
	void mousePressEvent(QMouseEvent * event);

signals:
	void NeedEdit(Event *);
	void NeedDelete(Event *);

public slots :
	void OnEdit();
	void OnDelete();
	void OnQuit();

private:
    Event *_Event;

    QHBoxLayout *_MainLayout;

    QLabel *_Label;

	QWidget *_Popup;
	QGridLayout *_MainLayoutPopup;
	QLabel *_Title;
	QLabel *_Description;
	QLabel *_Date;
	QLabel *_ProjectLinked;
	QPushButton *_Edit;
	QPushButton *_Delete;
	PushButtonImage *_Quit;
};

#endif // CALENDAREVENT_H
