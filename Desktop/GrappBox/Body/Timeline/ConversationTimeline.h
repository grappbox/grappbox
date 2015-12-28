#ifndef CONVERSATIONTIMELINE_H
#define CONVERSATIONTIMELINE_H

#include "MessageTimeLine.h"
#include <QVBoxLayout>
#include <QPushButton>
#include <QScrollArea>
#include <QMap>
#include <QWidget>

class ConversationTimeline : public QWidget
{
    Q_OBJECT
public:
    explicit ConversationTimeline(int id, bool revert, QWidget *parent = 0);
    void ForceCloseComment();
    int GetID() const;

signals:
    void NeedUpdateTimeline();
    void AnimOpenComment(int);

public slots:
    void TimeLineEdit(int);
    void TimeLineDelete(int);
    void OpenComment();

private:
    bool                _CommentHide;
    int                 _ConversationId;

private:
    QVBoxLayout         *_MainLayout;
    QVBoxLayout         *_CommentLayout;

    QWidget             *_MainCommentWidget;
    QScrollArea         *_ScrollAnim;

    QPushButton         *_OpenComment;

    MessageTimeLine     *_MainMessage;
    QMap<int, MessageTimeLine*>     _CommentMessage;
};

#endif // CONVERSATIONTIMELINE_H
