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
    void OnDayCheckedChange(bool value);
    void OnMonthCheckedChange(bool value);
    void OnWeekCheckedChange(bool value);

    void OnNext();
    void OnPrev();

    void OnProjectCheckChange();

    void OnCreate();

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

    QDate _LastDrawingDate;
    QDate _CurrentDrawingDate;

    QMap<QDate, QList<Event*> > _MapMonthEvent;
    QMap<int, QString> _Projects;

    QHBoxLayout *_MainLayout;
    QVBoxLayout *_CalendarLayout;
    QVBoxLayout *_SideBarLayout;
    QHBoxLayout *_TopBarLayout;
    QStackedLayout *_ViewCalendarLayout;

    QVBoxLayout *_ProjectChoiceLayout;

    QPushButton *_NewEvent;

    QLabel *_LabelMonthCalendar;
    QCalendarWidget *_MonthCalendarFixed;

    QLabel *_ProjectChoice;
    QMap<int, QCheckBox*> _ProjectChoiceCheckBox;

    QPushButton *_PreviousDate;
    QPushButton *_NextDate;
    QLabel *_CurrentDate;
    QPushButton *_ToDay;
    QPushButton *_ToWeek;
    QPushButton *_ToMonth;

    CalendarViewMonth *_ViewMonth;
    CalendarViewWeek *_ViewWeek;
    CalendarViewDay *_ViewDay;
};

#endif // BODYCALENDAR_H
