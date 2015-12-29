#ifndef CANVASTIMELINE_H
#define CANVASTIMELINE_H

#include "Body/Timeline/LineTimeline.h"
#include "Body/Timeline/ConversationTimeline.h"

#include <QWidget>
#include <QGridLayout>
#include <QVBoxLayout>
#include <QLineEdit>
#include <QTextEdit>
#include <QPushButton>
#include <QLabel>

#include <QList>

class CanvasTimeline : public QWidget
{
    Q_OBJECT
public:
    explicit CanvasTimeline(int idTimeline, QWidget *parent = 0);

signals:

public slots:
    void UpdateTimelineAnim(int Id);
    void AddingTimeline();

private:
    int             _IDTimeline;

    QWidget         *_TimelineContener;
    QGridLayout     *_MainTimelineLayout;
    QList<ConversationTimeline*>    _Conversation;

    QWidget         *_ContenerAddMessage;
    QVBoxLayout     *_LayoutAddMessage;
    QLabel          *_LabelAddMessage;
    QLabel          *_TitleLabel;
    QLineEdit       *_TitleMessage;
    QLabel          *_MessageLabel;
    QTextEdit       *_Message;
    QPushButton     *_ConfirmAddingMessage;
};

#endif // CANVASTIMELINE_H
