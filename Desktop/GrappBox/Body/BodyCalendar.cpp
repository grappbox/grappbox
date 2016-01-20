#include "BodyCalendar.h"

BodyCalendar::BodyCalendar()
{
    _CurrentDrawingDate = QDate::currentDate();

    _MainLayout = new QHBoxLayout();
    _MainLayout->setContentsMargins(0, 0, 0, 0);
    _MainLayout->setSpacing(0);

    _CalendarLayout = new QVBoxLayout();
    _CalendarLayout->setContentsMargins(0, 0, 0, 0);
    _CalendarLayout->setSpacing(0);

    _SideBarLayout = new QVBoxLayout();
    _SideBarLayout->setContentsMargins(0, 0, 0, 0);
    _SideBarLayout->setSpacing(0);

    _ViewCalendarLayout = new QStackedLayout();
    _ViewCalendarLayout->setContentsMargins(0, 0, 0, 0);
    _ViewCalendarLayout->setSpacing(0);

    _ProjectChoiceLayout = new QVBoxLayout();
    _ProjectChoiceLayout->setContentsMargins(0, 0, 0, 0);
    _ProjectChoiceLayout->setSpacing(0);

    _TopBarLayout = new QHBoxLayout();
    _TopBarLayout->setContentsMargins(0, 0, 0, 0);
    _TopBarLayout->setSpacing(0);

    _NewEvent = new QPushButton("Create");
    _NewEvent->setMaximumHeight(60);

    _LabelMonthCalendar = new QLabel(_CurrentDrawingDate.toString("MMMM, yyyy"));
    _LabelMonthCalendar->setMaximumHeight(20);
    _MonthCalendarFixed = new QCalendarWidget();
    _MonthCalendarFixed->setCurrentPage(_CurrentDrawingDate.year(), _CurrentDrawingDate.month());
    _MonthCalendarFixed->setDateEditEnabled(false);
    _MonthCalendarFixed->setNavigationBarVisible(false);
    _MonthCalendarFixed->setVerticalHeaderFormat(QCalendarWidget::NoVerticalHeader);
    _MonthCalendarFixed->setSelectionMode(QCalendarWidget::NoSelection);
    _MonthCalendarFixed->setMaximumHeight(200);

    _ProjectChoice = new QLabel("Projects");

    _PreviousDate = new QPushButton("<");
    _PreviousDate->setMaximumWidth(60);
    _NextDate = new QPushButton(">");
    _NextDate->setMaximumWidth(60);
    _CurrentDate = new QLabel(_CurrentDrawingDate.toString("MMMM, yyyy"));
    _CurrentDate->setAlignment(Qt::AlignCenter);
    _ToDay = new QPushButton("Day");
    _ToDay->setCheckable(true);
    _ToDay->setMaximumWidth(90);
    _ToWeek = new QPushButton("Week");
    _ToWeek->setMaximumWidth(90);
    _ToWeek->setCheckable(true);
    _ToMonth = new QPushButton("Month");
    _ToMonth->setMaximumWidth(90);
    _ToMonth->setCheckable(true);
    _ToMonth->setChecked(true);

    _ViewMonth = new CalendarViewMonth();

    QWidget *wSideBarLayout = new QWidget();
    wSideBarLayout->setLayout(_SideBarLayout);
    wSideBarLayout->setMaximumWidth(200);

    _MainLayout->addLayout(_CalendarLayout);
    _MainLayout->addWidget(wSideBarLayout);

    _SideBarLayout->addWidget(_NewEvent);
    _SideBarLayout->addWidget(_LabelMonthCalendar);
    _SideBarLayout->addWidget(_MonthCalendarFixed);
    _SideBarLayout->addWidget(_ProjectChoice);
    _SideBarLayout->addLayout(_ProjectChoiceLayout);
    _SideBarLayout->addSpacing(1080);

    QWidget *wTopBarLayout = new QWidget();
    wTopBarLayout->setLayout(_TopBarLayout);
    wTopBarLayout->setMaximumHeight(60);

    _CalendarLayout->addWidget(wTopBarLayout);
    _CalendarLayout->addLayout(_ViewCalendarLayout);

    _ViewCalendarLayout->addWidget(_ViewMonth);

    _TopBarLayout->addWidget(_PreviousDate);
    _TopBarLayout->addWidget(_CurrentDate);
    _TopBarLayout->addWidget(_NextDate);
    _TopBarLayout->addWidget(_ToDay);
    _TopBarLayout->addWidget(_ToWeek);
    _TopBarLayout->addWidget(_ToMonth);

    setLayout(_MainLayout);
}

void BodyCalendar::Show(int ID, MainWindow *mainApp)
{
    _WidgetId = ID;
    _MainApp = mainApp;
    emit OnLoadingDone(ID);
}

void BodyCalendar::Hide()
{
}
