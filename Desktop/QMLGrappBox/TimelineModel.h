#ifndef TIMELINEMODEL_H
#define TIMELINEMODEL_H

#include <QObject>
#include <QVariant>
#include "API/SDataManager.h"
#include "UserData.h"

class TimelineMessageData : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QString title READ title WRITE setTitle NOTIFY titleChanged)
    Q_PROPERTY(QString message READ message WRITE setMessage NOTIFY messageChanged)
    Q_PROPERTY(int id READ id WRITE setId NOTIFY idChanged)
    Q_PROPERTY(UserData *associatedUser READ associatedUser WRITE setAssociatedUser NOTIFY associatedUserChanged)
    Q_PROPERTY(bool isComment READ isComment WRITE setIsComment NOTIFY isCommentChanged)
    Q_PROPERTY(QVariantList comments READ comments WRITE setComments NOTIFY commentsChanged)
    Q_PROPERTY(QDateTime lastEdit READ lastEdit WRITE setLastEdit NOTIFY lastEditChanged)

public:
    explicit TimelineMessageData(QObject *parent = 0) : QObject(parent)
    {
        m_associatedUser = nullptr;
    }

    QString title() const
    {
        return m_title;
    }

    QString message() const
    {
        return m_message;
    }

    int id() const
    {
        return m_id;
    }

    UserData *associatedUser() const
    {
        return m_associatedUser;
    }

    bool isComment() const
    {
        return m_isComment;
    }

    QVariantList comments() const
    {
        QVariantList list;
        for (TimelineMessageData *item : m_comments)
        {
            if (item)
                list.push_back(qVariantFromValue(item));
        }
        return list;
    }

    QList<TimelineMessageData*> commentsList() const
    {
        return m_comments;
    }

    QDateTime lastEdit() const
    {
        return m_lastEdit;
    }

signals:

    void titleChanged(QString title);

    void messageChanged(QString message);

    void idChanged(int id);

    void associatedUserChanged(UserData *associatedUser);

    void isCommentChanged(bool isComment);

    void commentsChanged(QVariantList comments);

    void lastEditChanged(QDateTime lastEdit);

public slots:

    void setTitle(QString title)
    {
        if (m_title == title)
            return;

        m_title = title;
        emit titleChanged(title);
    }

    void setMessage(QString message)
    {
        if (m_message == message)
            return;

        m_message = message;
        emit messageChanged(message);
    }

    void setId(int id)
    {
        if (m_id == id)
            return;

        m_id = id;
        emit idChanged(id);
    }

    void setAssociatedUser(UserData *associatedUser)
    {
        m_associatedUser = associatedUser;
        emit associatedUserChanged(associatedUser);
    }

    void setIsComment(bool isComment)
    {
        if (m_isComment == isComment)
            return;

        m_isComment = isComment;
        emit isCommentChanged(isComment);
    }

    void setComments(QVariantList comments)
    {
        m_comments.clear();
        for (QVariant item : comments)
        {
            TimelineMessageData *itemT = qobject_cast<TimelineMessageData*>(item.value<TimelineMessageData*>());
            m_comments.push_back(itemT);
        }
        emit commentsChanged(comments);
    }

    void setComments(QList<TimelineMessageData*> commentsP)
    {
        m_comments = commentsP;
        emit commentsChanged(comments());
    }

    void setLastEdit(QDateTime lastEdit)
    {
        if (m_lastEdit == lastEdit)
            return;

        m_lastEdit = lastEdit;
        emit lastEditChanged(lastEdit);
    }

private:
    QString m_title;
    QString m_message;
    int m_id;
    UserData *m_associatedUser;
    bool m_isComment;
    QList<TimelineMessageData*> m_comments;
    QDateTime m_lastEdit;
};

class TimelineModel : public QObject
{
    Q_OBJECT

    Q_PROPERTY(QVariantList timelineClient READ timelineClient WRITE setTimelineClient NOTIFY timelineClientChanged)
    Q_PROPERTY(QVariantList timelineTeam READ timelineTeam WRITE setTimelineTeam NOTIFY timelineTeamChanged)
    Q_PROPERTY(bool isLoadingTimeline READ isLoadingTimeline WRITE setIsLoadingTimeline NOTIFY isLoadingTimelineChanged)
    Q_PROPERTY(bool isLoadingComment READ isLoadingComment WRITE setIsLoadingComment NOTIFY isLoadingCommentChanged)
    Q_PROPERTY(bool isLoadingAction READ isLoadingAction WRITE setIsLoadingAction NOTIFY isLoadingActionChanged)

    QList<TimelineMessageData*> m_timelineClient;

    QList<TimelineMessageData*> m_timelineTeam;

    bool m_isLoadingTimeline;

    bool m_isLoadingComment;

    bool m_isLoadingAction;

    int m_idTimelineClient;

    int m_idTimelineTeam;

    int m_numberLoading;

    QMap<int, int> m_loadComment;
    QMap<int, int> m_deleteComment;
    QMap<int, int> m_editComment;

public:
    explicit TimelineModel(QObject *parent = 0);

    QVariantList timelineClient() const
    {
        QVariantList list;
        for (TimelineMessageData *item : m_timelineClient)
        {
            if (item)
                list.push_back(qVariantFromValue(item));
        }
        return list;
    }

    QVariantList timelineTeam() const
    {
        QVariantList list;
        for (TimelineMessageData *item : m_timelineTeam)
        {
            if (item)
                list.push_back(qVariantFromValue(item));
        }
        return list;
    }

    bool isLoadingTimeline() const
    {
        return m_isLoadingTimeline;
    }

    bool isLoadingComment() const
    {
        return m_isLoadingComment;
    }

    bool isLoadingAction() const
    {
        return m_isLoadingAction;
    }

    Q_INVOKABLE void loadNextTimelineContent(bool isClient);
    Q_INVOKABLE void loadComments(bool isClient, int id);
    Q_INVOKABLE void addMessageTimeline(bool isClient, QString title, QString message);
    Q_INVOKABLE void addMessageTimeline(int idParent, QString message);
    Q_INVOKABLE void deleteMessageTimeline(int id, int parentId = -1);
    Q_INVOKABLE void editMessageTimeline(int parentId, int id, QString title, QString message);
    Q_INVOKABLE void loadTimelines();
    Q_INVOKABLE void addTicket(QString title, QString message);

signals:

    void timelineClientChanged(QVariantList timelineClient);

    void timelineTeamChanged(QVariantList timelineTeam);

    void isLoadingTimelineChanged(bool isLoadingTimeline);

    void isLoadingCommentChanged(bool isLoadingComment);

    void isLoadingActionChanged(bool isLoadingAction);

    void closeCommentIfId(int id);

    void editSuccess();
    void deleteSuccess();

public slots:
    void setTimelineClient(QVariantList timelineClient)
    {
        m_timelineClient.clear();
        for (QVariant item : timelineClient)
        {
            TimelineMessageData *itemT = qobject_cast<TimelineMessageData*>(item.value<TimelineMessageData*>());
            m_timelineClient.push_back(itemT);
        }
        emit timelineClientChanged(timelineClient);
    }

    void setTimelineTeam(QVariantList timelineTeam)
    {
        m_timelineTeam.clear();
        for (QVariant item : timelineTeam)
        {
            TimelineMessageData *itemT = qobject_cast<TimelineMessageData*>(item.value<TimelineMessageData*>());
            m_timelineTeam.push_back(itemT);
        }
        emit timelineTeamChanged(timelineTeam);
    }
    void setIsLoadingTimeline(bool isLoadingTimeline)
    {
        if (m_isLoadingTimeline == isLoadingTimeline)
            return;

        m_isLoadingTimeline = isLoadingTimeline;
        emit isLoadingTimelineChanged(isLoadingTimeline);
    }
    void setIsLoadingComment(bool isLoadingComment)
    {
        if (m_isLoadingComment == isLoadingComment)
            return;

        m_isLoadingComment = isLoadingComment;
        emit isLoadingCommentChanged(isLoadingComment);
    }
    void setIsLoadingAction(bool isLoadingAction)
    {
        if (m_isLoadingAction == isLoadingAction)
            return;

        m_isLoadingAction = isLoadingAction;
        emit isLoadingActionChanged(isLoadingAction);
    }

    void OnTimelineLoadDone(int id, QByteArray data);
    void OnTimelineLoadFail(int id, QByteArray data);
    void OnTimelineCommentLoadDone(int id, QByteArray data);
    void OnTimelineCommentLoadFail(int id, QByteArray data);
    void OnTimelineAddMessageDone(int id, QByteArray data);
    void OnTimelineAddMessageFail(int id, QByteArray data);
    void OnTimelineRemoveMessageDone(int id, QByteArray data);
    void OnTimelineRemoveMessageFail(int id, QByteArray data);
    void OnTimelineEditMessageDone(int id, QByteArray data);
    void OnTimelineEditMessageFail(int id, QByteArray data);
    void OnGetTimelineDone(int id, QByteArray data);
    void OnGetTimelineFail(int id, QByteArray data);
    void OnAddTicketDone(int id, QByteArray data);
    void OnAddTicketFail(int id, QByteArray data);


};

#endif // TIMELINEMODEL_H
