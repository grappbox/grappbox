#ifndef BODYTIMELINE_H
#define BODYTIMELINE_H

#include "IBodyContener.h"
#include "Body/Timeline/ConversationTimeline.h"

#include <QGridLayout>
#include <QStackedLayout>
#include <QScrollArea>
#include <QList>

#include <QWidget>
#include <QPushButton>

class BodyTimeline : public QWidget, public IBodyContener
{
    Q_OBJECT
public:
    explicit BodyTimeline(QWidget *parent = 0);
    virtual void Show(int ID, MainWindow *mainApp);
    virtual void Hide();

signals:
    void OnLoadingDone(int);

public slots:
    void UpdateTimelineAnim(int Id);

private:
    QStackedLayout     *_MainLayout;

    QScrollArea         *_SliderTimeline;
    QWidget         *_TimelineContener;
    QGridLayout     *_MainTimelineLayout;
    QList<ConversationTimeline*>    _Conversation;
};

#endif // BODYTIMELINE_H
