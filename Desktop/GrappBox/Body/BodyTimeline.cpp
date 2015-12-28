#include "Timeline/LineTimeline.h"
#include "BodyTimeline.h"

BodyTimeline::BodyTimeline(QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QStackedLayout();
    _MainTimelineLayout = new QGridLayout();
    _SliderTimeline = new QScrollArea();
    _TimelineContener = new QWidget();

    _MainTimelineLayout->setSpacing(0);
    _MainTimelineLayout->setContentsMargins(5, 5, 0, 0);

    for (int i = 0; i < 5; ++i)
    {
        ConversationTimeline *c = new ConversationTimeline(i, this);
        _Conversation.append(c);
        _MainTimelineLayout->addWidget(c, i, (i % 2) * 2, 1, 1);
        _MainTimelineLayout->addWidget(new LineTimeline(), i, 1, 1, 1);
        QObject::connect(c, SIGNAL(AnimOpenComment(int)), this, SLOT(UpdateTimelineAnim(int)));
    }
    _TimelineContener->setLayout(_MainTimelineLayout);
    _SliderTimeline->setWidget(_TimelineContener);
    _SliderTimeline->setWidgetResizable(true);
    _SliderTimeline->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
    _SliderTimeline->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);

    _MainLayout->addWidget(_SliderTimeline);

    setLayout(_MainLayout);
    setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);
}

void BodyTimeline::Show(int ID, MainWindow *mainApp)
{
    emit OnLoadingDone(ID);
}

void BodyTimeline::Hide()
{

}
#include <QDebug>
void  BodyTimeline::UpdateTimelineAnim(int Id)
{
    for (ConversationTimeline *item : _Conversation)
    {
        if (item->GetID() != Id)
            item->ForceCloseComment();
    }
}

