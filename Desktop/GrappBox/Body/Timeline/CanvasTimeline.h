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

#include <QMap>
#include <QList>

class CanvasTimeline : public QWidget
{
    Q_OBJECT
public:
    explicit CanvasTimeline(QWidget *parent = 0);
    void LoadData(int id);

private:
    void FinishedLoad();

signals:
    void OnFinishedLoading(int id);
    void OnDeleteMainMessage(int id);

public slots:
    void UpdateTimelineAnim(int Id);
    void AddingTimeline();
    void DeleteMessage(int id);

    void TimelineGetDone(int id, QByteArray array);
    void TimelineGetFailed(int id, QByteArray array);

    void TimelineGetUserDone(int id, QByteArray array);
    void TimelineGetUserFailed(int id, QByteArray array);

    void TimelineAddMessageDone(int id, QByteArray array);
    void TimelineAddMessageFailed(int id, QByteArray array);

private:
    int             _IDTimeline;

    int             _TotalLoad;

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

    QList<MessageTimeLine::MessageTimeLineInfo> _Messages;
    QMap<int, API::UserInformation> _Users;

};

#endif // CANVASTIMELINE_H
