#include <QVariant>
#include <QDebug>
#include "CalendarEventMonth.h"

CalendarEventMonth::CalendarEventMonth(QDate date, QWidget *parent) : QWidget(parent)
{
    _Date = date;
    _MainLayout = new QVBoxLayout();
    _EventLayout = new QVBoxLayout();

    _EventLayout->setAlignment(Qt::AlignLeft | Qt::AlignTop);

    _DayNumberLabel = new QLabel(date.toString("dd"));
    _DayNumberLabel->setAlignment(Qt::AlignLeft | Qt::AlignTop);
    _DayNumberLabel->setFixedHeight(20);
    _MainLayout->addWidget(_DayNumberLabel);

    QWidget *w = new QWidget();
    w->setLayout(_EventLayout);

    _MainLayout->addWidget(w);

    setObjectName("CalendarEventMonth");
    setStyleSheet("CalendarEventMonth {border-style: solid; border-color: #d8d8d8; border-width: 1px;}");

    setLayout(_MainLayout);
}

void CalendarEventMonth::LoadEvents(const QList<Event*> &event, QDate date)
{
    _Date = date;
    _DayNumberLabel->setText(date.toString("dd"));
    for (Event *env : event)
    {
        if (env->Start.date() >= _Date && env->End.date() <= _Date)
        {
            _Events.push_back(env);
        }
    }
    int i = 0;
    for (Event *env : _Events)
    {
        if (i == 5)
        {
            QLabel *plusEnv = new QLabel("+" + QVariant(_Events.size() - 5).toString() + " events...");
            plusEnv->setStyleSheet("color: #00AA00;");
            _EventLayout->addWidget(plusEnv);
            break;
        }
        else
        {
            QLabel *newEnv = new QLabel(env->Start.time().toString("[hh:mm] ") + env->Title);
            newEnv->setStyleSheet("color: " + env->Color.name() + ";");
            _EventLayout->addWidget(newEnv);
        }
        ++i;
    }
}

void CalendarEventMonth::paintEvent(QPaintEvent *event)
{
    QWidget::paintEvent(event);
    QStyleOption *opt = new QStyleOption();
    opt->initFrom(this);
    QPainter p(this);
    QStyle *s = style();
    s->drawPrimitive(QStyle::PE_Widget, opt, &p, this);
}
