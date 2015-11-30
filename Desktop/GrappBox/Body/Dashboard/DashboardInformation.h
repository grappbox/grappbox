#ifndef DASHBOARDINFORMATION_H
#define DASHBOARDINFORMATION_H

#include <QPixmap>
#include <QString>

namespace DashboardInformation
{
    struct MemberAvaiableInfo
    {
        MemberAvaiableInfo(QString memberName, bool isBusy, QPixmap *memberPicture = NULL);

        QString     MemberName;
        bool        IsBusy;
        QPixmap      *MemberPicture;
        int         Id;
    };

    struct NextMeetingInfo
    {
        // This enum is temporary, It will be updated when the meeting part will be created
        enum NextMeetingTypeInfo
        {
            Client,
            Personnal,
            Company
        };

        NextMeetingInfo(NextMeetingTypeInfo type, QString meetingName, QString date, QString hours, QPixmap *projectIcon = NULL);

        NextMeetingTypeInfo Type;
        QString             MeetingName;
        QPixmap             *ProjectIcon;
        QString             Date;
        QString             Hours;
    };

    struct GlobalProgressInfo
    {
        GlobalProgressInfo(QString projectTitle, QString projectCompany, QString projectTel,
                           QString projectMail, int numberMaxTask, int numberTask, int numberMsg,
                           int numberProblem, QPixmap *projectPicture = NULL);

        QString ProjectTitle;
        QString ProjectCompany;
        QString ProjectTel;
        QString ProjectMail;
        int MaxNumberTask;\
        int NumberTask;
        int NumberMsg;
        int NumberProblem;
        QPixmap *ProjectPicture;
    };
}

#endif // DASHBOARDINFORMATION_H
