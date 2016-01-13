#ifndef CONVERSATIONTIMELINE_H
#define CONVERSATIONTIMELINE_H

#include "MessageTimeLine.h"
#include <QVBoxLayout>
#include <QPushButton>
#include <QScrollArea>
#include <QMap>
#include <QWidget>

#include <QVector>
#include <QList>
#include <QMap>
#include <QTimer>
#include "SDataManager.h"

class ConversationTimeline : public QWidget
{
    Q_OBJECT
public:
    explicit ConversationTimeline(int timelineId, MessageTimeLine::MessageTimeLineInfo data, bool revert, QWidget *parent = 0);
    void ForceCloseComment();
    int GetID() const;

    virtual void update();

signals:
    void NeedUpdateTimeline();
    void AnimOpenComment(int);
    void OnDeleteMainMessage(int);


public slots:
    void NewComment(MessageTimeLine::MessageTimeLineInfo info);
    void TimeLineDelete(int);
    void OpenCommentAnim();
    void OpenComment();

    void TimelineGetDone(int id, QByteArray array);
    void TimelineGetFailed(int id, QByteArray array);

    void TimelineGetUserDone(int id, QByteArray array);
    void TimelineGetUserFailed(int id, QByteArray array);

    void OnAnimEnd();

private:
    void                FinishedLoad();

    bool                _CommentHide;
    int                 _ConversationId;
    int                 _TimelineId;

    bool                _ReloadContain;

private:
    QVBoxLayout         *_MainLayout;
    QVBoxLayout         *_CommentLayout;

    QWidget             *_MainCommentWidget;
    QScrollArea         *_ScrollAnim;

    QPushButton         *_OpenComment;
    MessageTimeLine     *_CommentMessageNew;

    MessageTimeLine     *_MainMessage;
    QMap<int, MessageTimeLine*>     _CommentMessage;

    QList<MessageTimeLine::MessageTimeLineInfo> _Messages;
    QMap<int, API::UserInformation> _Users;
};

#endif // CONVERSATIONTIMELINE_H
