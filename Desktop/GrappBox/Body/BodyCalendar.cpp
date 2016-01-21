#include <QDebug>
#include <QScrollArea>
#include "BodyCalendar.h"

BodyCalendar::BodyCalendar()
{
    _CurrentDrawingDate = QDate::currentDate();
    _LastDrawingDate = QDate::currentDate();

    _View = MONTH;

    _MainLayout = new QHBoxLayout();
    _MainLayout->setContentsMargins(0, 0, 0, 0);
    _MainLayout->setSpacing(0);

    _CalendarLayout = new QVBoxLayout();
    _CalendarLayout->setContentsMargins(0, 0, 0, 0);
    _CalendarLayout->setSpacing(0);

    _SideBarLayout = new QVBoxLayout();
    _SideBarLayout->setContentsMargins(10, 0, 0, 0);
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
    _NewEvent->setMaximumHeight(80);

    _LabelMonthCalendar = new QLabel(_CurrentDrawingDate.toString("MMMM, yyyy"));
    _LabelMonthCalendar->setMaximumHeight(20);
    _MonthCalendarFixed = new QCalendarWidget();
    _MonthCalendarFixed->setCurrentPage(_CurrentDrawingDate.year(), _CurrentDrawingDate.month());
    _MonthCalendarFixed->setDateEditEnabled(false);
    _MonthCalendarFixed->setNavigationBarVisible(false);
    _MonthCalendarFixed->setVerticalHeaderFormat(QCalendarWidget::NoVerticalHeader);
    _MonthCalendarFixed->setSelectionMode(QCalendarWidget::NoSelection);
    _MonthCalendarFixed->setMaximumHeight(200);
    _MonthCalendarFixed->setFirstDayOfWeek(Qt::Monday);

    _ProjectChoice = new QLabel("Projects");

    _PreviousDate = new QPushButton("<");
    _PreviousDate->setMaximumWidth(60);
    _NextDate = new QPushButton(">");
    _NextDate->setMaximumWidth(60);
    _CurrentDate = new QLabel(_CurrentDrawingDate.toString("MMMM, yyyy"));
    _CurrentDate->setAlignment(Qt::AlignCenter);
    _CurrentDate->setMinimumHeight(20);
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
    _ToMonth->setDisabled(true);

    _ViewMonth = new CalendarViewMonth();
    _ViewWeek = new CalendarViewWeek();
    _ViewDay = new CalendarViewDay();

    QScrollArea *_WeekScrollArea = new QScrollArea();
    _WeekScrollArea->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);
    _WeekScrollArea->setWidgetResizable(true);
    _WeekScrollArea->setWidget(_ViewWeek);
    QScrollArea *_DayScrollArea = new QScrollArea();
    _DayScrollArea->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
    _DayScrollArea->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);
    _DayScrollArea->setWidgetResizable(true);
    _DayScrollArea->setWidget(_ViewDay);

    QWidget *wSideBarLayout = new QWidget();
    wSideBarLayout->setLayout(_SideBarLayout);
    wSideBarLayout->setFixedWidth(300);

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
    _ViewCalendarLayout->addWidget(_WeekScrollArea);
    _ViewCalendarLayout->addWidget(_DayScrollArea);

    _TopBarLayout->addWidget(_PreviousDate);
    _TopBarLayout->addWidget(_CurrentDate);
    _TopBarLayout->addWidget(_NextDate);
    _TopBarLayout->addWidget(_ToDay);
    _TopBarLayout->addWidget(_ToWeek);
    _TopBarLayout->addWidget(_ToMonth);

    setLayout(_MainLayout);

    _Projects[11] = "GrappBox";
    _Projects[5] = "Doulan";
    _Projects[2] = "Gundolf";

    for (QMap<int, QString>::iterator it = _Projects.begin(); it != _Projects.end(); ++it)
    {
        QCheckBox *checkBox = new QCheckBox(it.value());
        checkBox->setChecked(true);
        _ProjectChoiceCheckBox[it.key()] = checkBox;
        _ProjectChoiceLayout->addWidget(checkBox);
        QObject::connect(checkBox, SIGNAL(toggled(bool)), this, SLOT(OnProjectCheckChange()));
    }

    QColor color[3] = {QColor(210, 18, 230), QColor(140, 30, 10), QColor(30, 250, 10)};

    for (int i = 0; i < 200; ++i)
    {
        Event *event = new Event();
        event->Title = "Event";
        int hour = qrand() % 21;
        int minute = qrand() % 60;
        int month = qrand() % 3;
        event->Start.setDate(QDate(2016, month + 1, qrand() % 29));
        event->Start.setTime(QTime(hour, minute));
        event->End.setDate(event->Start.date());
        event->End.setTime(QTime(hour + 2, minute));
        event->EventId = i;

        int mapIdProject = qrand() % _Projects.size();
        event->Color = color[mapIdProject];
        for (QMap<int, QString>::iterator it = _Projects.begin(); it != _Projects.end(); ++it)
        {
            if (mapIdProject == 0)
            {
                event->ProjectId = it.key();
                break;
            }
            mapIdProject--;
        }



        _MapMonthEvent[QDate(2016, month + 1, 1)].push_back(event);
    }
    UpdateType();

    QObject::connect(_ToDay, SIGNAL(clicked(bool)), this, SLOT(OnDayCheckedChange(bool)));
    QObject::connect(_ToWeek, SIGNAL(clicked(bool)), this, SLOT(OnWeekCheckedChange(bool)));
    QObject::connect(_ToMonth, SIGNAL(clicked(bool)), this, SLOT(OnMonthCheckedChange(bool)));

    QObject::connect(_NextDate, SIGNAL(clicked(bool)), this, SLOT(OnNext()));
    QObject::connect(_PreviousDate, SIGNAL(clicked(bool)), this, SLOT(OnPrev()));
}

void BodyCalendar::OnWeekCheckedChange(bool value)
{
    qDebug() << value;
    _ToMonth->setDisabled(false);
    _ToDay->setDisabled(false);
    _ToWeek->setDisabled(true);
    _ToMonth->setChecked(false);
    _ToDay->setChecked(false);
    if (_View != WEEK)
    {
        _View = WEEK;
        UpdateType();
    }
}

void BodyCalendar::OnMonthCheckedChange(bool value)
{
    qDebug() << value;
    _ToMonth->setDisabled(true);
    _ToDay->setDisabled(false);
    _ToWeek->setDisabled(false);
    _ToDay->setChecked(false);
    _ToWeek->setChecked(false);
    if (_View != MONTH)
    {
        _View = MONTH;
        UpdateType();
    }
}

void BodyCalendar::OnDayCheckedChange(bool value)
{
    qDebug() << value;
    _ToMonth->setDisabled(false);
    _ToDay->setDisabled(true);
    _ToWeek->setDisabled(false);
    _ToMonth->setChecked(false);
    _ToWeek->setChecked(false);
    if (_View != DAY)
    {
        _View = DAY;
        UpdateType();
    }
}

void BodyCalendar::OnNext()
{
    qDebug() << "Before next : " << _CurrentDrawingDate.toString("yyyy/MM/dd");
    switch (_View)
    {
    case DAY:
        _CurrentDrawingDate = _CurrentDrawingDate.addDays(1);
        break;
    case MONTH:
        _CurrentDrawingDate = _CurrentDrawingDate.addMonths(1);
        break;
    case WEEK:
        _CurrentDrawingDate = _CurrentDrawingDate.addDays(7);
        break;
    }
    qDebug() << "After next : " << _CurrentDrawingDate.toString("yyyy/MM/dd");
    UpdateType();
}

void BodyCalendar::OnPrev()
{
    switch (_View)
    {
    case DAY:
        _CurrentDrawingDate = _CurrentDrawingDate.addDays(-1);
        break;
    case MONTH:
        _CurrentDrawingDate = _CurrentDrawingDate.addMonths(-1);
        break;
    case WEEK:
        _CurrentDrawingDate = _CurrentDrawingDate.addDays(-7);
        break;
    }
    UpdateType();
}

void BodyCalendar::OnProjectCheckChange()
{
    QCheckBox *sender = dynamic_cast<QCheckBox*>(QObject::sender());
    if (sender == NULL)
        return;
    for (QMap<int, QCheckBox*>::iterator it = _ProjectChoiceCheckBox.begin(); it != _ProjectChoiceCheckBox.end(); ++it)
    {
        if (it.value() == sender)
        {
            if (sender->isChecked())
            {
                _ViewMonth->ShowProject(it.key());
                _ViewWeek->ShowProject(it.key());
                _ViewDay->ShowProject(it.key());
            }
            else
            {
                _ViewMonth->HideProject(it.key());
                _ViewWeek->HideProject(it.key());
                _ViewDay->HideProject(it.key());
            }
        }
    }
}

void BodyCalendar::UpdateType()
{
    if (_LastDrawingDate.month() != _CurrentDrawingDate.month())
    {
        QDate keyDate = QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month(), 1);
        if (!_MapMonthEvent.contains(keyDate))
        {
            // Do call API for loading a new month and recall UpdateType
            //TO_DELETE
            _LastDrawingDate = _CurrentDrawingDate;
            //END TO_DELETE
            //return;
        }
        _ViewMonth->LoadEvents(_MapMonthEvent[keyDate], _CurrentDrawingDate);
        _MonthCalendarFixed->setCurrentPage(_CurrentDrawingDate.year(), _CurrentDrawingDate.month());
    }
    switch (_View)
    {
    case DAY:
        _CurrentDate->setText(_CurrentDrawingDate.toString("dddd, dd MMMM yyyy"));
        break;
    case MONTH:
        _CurrentDate->setText(_CurrentDrawingDate.toString("MMMM yyyy"));
        break;
    case WEEK:
        QDate mondayDate = _CurrentDrawingDate;
        while (mondayDate.dayOfWeek() != 1)
            mondayDate = mondayDate.addDays(-1);
        QDate sundayDate = mondayDate.addDays(6);
        QString firstDate = "";
        if (mondayDate.year() != sundayDate.year())
            firstDate = mondayDate.toString("dd MMMM yyyy");
        else if (mondayDate.month() != sundayDate.month())
            firstDate = mondayDate.toString("dd MMMM");
        else
            firstDate = mondayDate.toString("dd");
        _CurrentDate->setText(firstDate + " - " + sundayDate.toString("dd MMMM yyyy"));
        break;
    }
    _LabelMonthCalendar->setText(_CurrentDrawingDate.toString("MMMM, yyyy"));
    QList<Event*> currentEvents = _MapMonthEvent[QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month(), 1)];
    if (_CurrentDrawingDate.month() == 1)
        currentEvents.append(_MapMonthEvent[QDate(_CurrentDrawingDate.year() - 1, 12, 1)]);
    else
        currentEvents.append(_MapMonthEvent[QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month() - 1, 1)]);
    if (_CurrentDrawingDate.month() == 12)
        currentEvents.append(_MapMonthEvent[QDate(_CurrentDrawingDate.year() + 1, 1, 1)]);
    else
        currentEvents.append(_MapMonthEvent[QDate(_CurrentDrawingDate.year(), _CurrentDrawingDate.month() + 1, 1)]);
    switch (_View)
    {
    case DAY:
        _ViewDay->LoadEvents(currentEvents, _CurrentDrawingDate);
        _ViewCalendarLayout->setCurrentIndex(2);
        break;
    case MONTH:
        _ViewMonth->LoadEvents(currentEvents, _CurrentDrawingDate);
        _ViewCalendarLayout->setCurrentIndex(0);
        break;
    case WEEK:
        _ViewWeek->LoadEvents(currentEvents, _CurrentDrawingDate);
        _ViewCalendarLayout->setCurrentIndex(1);
        break;
    }

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
