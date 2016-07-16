#include "InfoPushButton.h"

InfoPushButton::InfoPushButton(QWidget *parent) : QPushButton(parent)
{
    QObject::connect(this, SIGNAL(released()), this, SLOT(ReleaseSlot()));
}

void InfoPushButton::SetInfo(int info)
{
    _info = info;
}

int InfoPushButton::GetInfo()
{
    return (_info);
}

void InfoPushButton::ReleaseSlot()
{
    emit ReleaseInfo(_info);
}
