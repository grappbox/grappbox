#ifndef BODYCALENDAR_H
#define BODYCALENDAR_H

#include "ibodycontener.h"
#include "Calendar/CalendarEvent.h"
#include "Calendar/CalendarViewMonth.h"
#include "Calendar/CalendarViewWeek.h"

#include <QStackedLayout>
#include <QCheckBox>
#include <QLabel>
#include <QCalendarWidget>
#include <QPushButton>

#include <QDate>
#include <QMap>

struct Task
{
	int TaskId;
	QString Title;
	QDateTime Start;
	QDateTime End;
	int ProjectId;

	bool operator==(const Task &task) const
	{
		return (TaskId == task.TaskId);
	}
};

class BodyCalendar : public QWidget, public IBodyContener
{
    Q_OBJECT

public:
    BodyCalendar();

    virtual void Show(int ID, MainWindow *mainApp);
    virtual void Hide();

signals:
    void OnLoadingDone(int);

public slots:
	void OnEventLoadingDone(int, QByteArray data);
	void OnEventLoadingFail(int, QByteArray data);

	void OnProjectLoadingDone(int requestId, QByteArray data);
	void OnProjectLoadingFail(int, QByteArray data);

    void OnDayCheckedChange(bool value);
    void OnMonthCheckedChange(bool value);
    void OnWeekCheckedChange(bool value);

    void OnNext();
    void OnPrev();

    void OnProjectCheckChange();

    void OnCreate();

	void OnMoveToday();

	void OnEditEvent(Event *event);
	void OnDeleteEvent(Event *event);

	void OnDeleteDone(int id, QByteArray data);
	void OnDeleteFail(int id, QByteArray data);

	void OnLoadingProjectsDone(int id, QByteArray data);
	void OnLoadingProjectsFail(int id, QByteArray data);

private:
    enum ViewType
    {
        MONTH = 0,
        WEEK = 1,
        DAY = 2
    };

    void UpdateType();

private:
    MainWindow *_MainApp;
    int _WidgetId;
    ViewType _View;
	bool _IsLoaded;
	bool _IsProjectsLoaded;

	QMap<int, QDate> _LoadingDates;
	QMap<int, int> _LoadingProjects;

	QMap<int, QString> _AllProjects;
	QMap<int, QString> _ProjectsColors;

	QMap<int, int> _DeleteEvent;

    QDate _LastDrawingDate;
    QDate _CurrentDrawingDate;

    QMap<QDate, QList<Event*> > _MapMonthEvent;
	QList<Task*> _MapMonthTask;

    QMap<int, QString> _Projects;

    QHBoxLayout *_MainLayout;
    QVBoxLayout *_CalendarLayout;
    QVBoxLayout *_SideBarLayout;
    QHBoxLayout *_TopBarLayout;
    QStackedLayout *_ViewCalendarLayout;

    QVBoxLayout *_ProjectChoiceLayout;
	QVBoxLayout *_TaskChoiceLayout;

    QPushButton *_NewEvent;

    QLabel *_LabelMonthCalendar;
    QCalendarWidget *_MonthCalendarFixed;

    QLabel *_ProjectChoice;
    QMap<int, QCheckBox*> _ProjectChoiceCheckBox;

	QLabel *_TaskChoice;

    QPushButton *_PreviousDate;
    QPushButton *_NextDate;
    QLabel *_CurrentDate;
    QPushButton *_ToDay;
    QPushButton *_ToWeek;
    QPushButton *_ToMonth;
	QPushButton *_ToToday;

    CalendarViewMonth *_ViewMonth;
    CalendarViewWeek *_ViewWeek;
	CalendarViewDay *_ViewDay;
};

#endif // BODYCALENDAR_H
