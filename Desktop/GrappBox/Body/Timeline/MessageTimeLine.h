#ifndef MESSAGETIMELINE_H
#define MESSAGETIMELINE_H

#include "SDataManager.h"

#include <QGridLayout>
#include <QStackedLayout>
#include <QVBoxLayout>
#include <QHBoxLayout>

#include <QDateTime>
#include <QLabel>
#include <QTextEdit>
#include <QLineEdit>
#include "WidgetCommon/PushButtonImage.h"
#include <QPushButton>
#include <QWidget>

class MessageTimeLine : public QWidget
{
public:
    struct MessageTimeLineInfo
    {
    public:
        MessageTimeLineInfo(int idTimeline, int idParent, QString title, QString message, QDateTime lastModification, int idUser, QImage *avatar, QString name, QString lastName)
        {
            IdTimeline = idTimeline;
            IdParent = idParent;
            Title = title;
            Message = message;
            DateLastModification = lastModification;
            IdUser = idUser;
            Avatar = avatar;
            Name = name;
            LastName = lastName;
        }
        MessageTimeLineInfo()
        {
            MessageTimeLineInfo(-1, false, "", "", QDateTime(), -1, NULL, "", "");
        }

        bool operator==(MessageTimeLineInfo const& b)
        {
            return (IdTimeline == b.IdTimeline);
        }

        int IdTimeline;
        int IdParent;
        QString Title;
        QString Message;
        QDateTime DateLastModification;
        int IdUser;
        QImage *Avatar;
        QString Name;
        QString LastName;
    };

private:
    Q_OBJECT
public:
    MessageTimeLine(MessageTimeLineInfo data, int IdTimeline, QWidget *parent = 0);

signals:
    void TimelineDeleted(int);
    void NewMessage(MessageTimeLine::MessageTimeLineInfo info);

public slots:
    void OnEdit();
    void OnRemove();
    void OnCancelEdit();
    void OnConfirmEdit();

    void OnEditDone(int id, QByteArray data);
    void OnEditFail(int id, QByteArray data);
    void OnDeleteDone(int id, QByteArray data);
    void OnDeleteFail(int id, QByteArray data);

private:
    int                 _IDTimelineMessage;
    int                 _IDTimeline;
    int                 _IDUserCreator;

    int                 _IDLayoutNormal;
    int                 _IDLayoutEdit;

    QString             _BeforeAPITitle;
    QString             _BeforeAPIMessage;

private:
    QGridLayout         *_MainLayoutLoading;
    QStackedLayout      *_MainLayout;

    QLabel              *_LoadingImage;

    QGridLayout         *_MainLayoutNormal;
    QLabel              *_Avatar;
    QLabel              *_Title;
    QLabel              *_Message;
    QLabel              *_Date;
    PushButtonImage         *_EditButton;
    PushButtonImage         *_RemoveButton;

    QVBoxLayout         *_MainLayoutEdit;
    QHBoxLayout         *_ButtonLayout;
    QLineEdit              *_TitleEdit;
    QTextEdit           *_EditMessageArea;
    PushButtonImage         *_ValidateButton;
    PushButtonImage         *_CancelButton;

    MessageTimeLine::MessageTimeLineInfo _MessageData;

};

#endif // MESSAGETIMELINE_H
