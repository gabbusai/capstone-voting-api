<div style="
    max-width: 100%; 
    width: 700px; 
    margin: auto; 
    border-radius: 20px; 
    background-color: #3B82F6; 
    padding: 30px; 
    color: #ffffff; 
    font-family: Arial, sans-serif;">

    <h2 style="
        font-size: 26px; 
        margin-bottom: 20px; 
        text-align: center;">Hello, {{ $user->name }}</h2>
    
    <p style="
        font-size: 18px; 
        line-height: 1.6; 
        text-align: center;">
        Thank you for verifying with us! To complete your verification, please use the verification code below:
    </p>
    
    <div style="margin-top: 30px; margin-bottom: 30px; text-align: center;">
        <p style="
            font-size: 24px; 
            font-weight: bold; 
            background-color: #ffffff; 
            color: #3B82F6; 
            padding: 15px 30px; 
            border-radius: 8px; 
            display: inline-block;">
            {{ $code }}
        </p>
    </div>
    
    <p style="
        font-size: 16px; 
        line-height: 1.6; 
        text-align: center;">
        Note: This code will expire in 15 minutes!
    </p>
    
    <div style="
        margin-top: 40px; 
        padding-top: 20px; 
        border-top: 1px solid #ffffff; 
        text-align: center;">
        <p style="font-size: 14px; color: #e0e0e0;">Best Regards,</p>
        <p style="font-size: 14px; color: #e0e0e0;">SPCF Voting Team</p>
    </div>
</div>

<!-- Responsive Styling -->
<style>
    /* Make the email content more mobile-friendly */
    @media (max-width: 600px) {
        div {
            padding: 20px;
        }
        h2 {
            font-size: 22px;
        }
        p {
            font-size: 16px;
        }
        .code-box {
            font-size: 20px;
            padding: 10px 20px;
        }
    }
</style>
