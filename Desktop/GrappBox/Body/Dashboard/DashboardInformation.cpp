#include "DashboardInformation.h"

using namespace DashboardInformation;

MemberAvaiableInfo::MemberAvaiableInfo(QString memberName, bool isBusy, QPixmap *memberPicture)
{
    MemberName = memberName;
    IsBusy = isBusy;
    MemberPicture = memberPicture;
}

NextMeetingInfo::NextMeetingInfo(NextMeetingTypeInfo type, QString meetingName, QString date,
                                 QString hours, QPixmap *projectIcon)
{
    Type = type;
    MeetingName = meetingName;
    ProjectIcon = projectIcon;
    Date = date;
    Hours = hours;
}

GlobalProgressInfo::GlobalProgressInfo(QString projectTitle, QString projectCompany, QString projectTel, QString projectMail, int numberMaxTask, int numberTask, int numberMsg, int numberProblem, QPixmap *projectPicture)
{
    ProjectCompany = projectCompany;
    ProjectMail = projectMail;
    ProjectPicture = projectPicture;
    ProjectTel = projectTel;
    ProjectTitle = projectTitle;
    MaxNumberTask = numberMaxTask;
    NumberTask = numberTask;
    NumberMsg = numberMsg;
    NumberProblem = numberProblem;
}
