#ifndef MESSAGETIMELINE_H
#define MESSAGETIMELINE_H

#include "SDataManager.h"

#include <QGridLayout>
#include <QStackedLayout>
#include <QVBoxLayout>
#include <QHBoxLayout>

#include <QLabel>
#include <QTextEdit>
#include <QLineEdit>
#include "WidgetCommon/PushButtonImage.h"
#include <QPushButton>
#include <QWidget>

class MessageTimeLine : public QWidget
{
    Q_OBJECT
public:
    MessageTimeLine(int idTimeline, QWidget *parent = 0);

signals:
    void OnLoadingDone(int);
    void OnLoadingError(int);
    void TimelineEdited(int);
    void TimelineDeleted(int);

public slots:
    void OnEdit();
    void OnRemove();
    void OnCancelEdit();
    void OnConfirmEdit();

private:
    int                 _IDTimeline;
    int                 _IDUserCreator;

    int                 _IDLayoutNormal;
    int                 _IDLayoutEdit;

private:
    QStackedLayout      *_MainLayout;

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
};

#endif // MESSAGETIMELINE_H
